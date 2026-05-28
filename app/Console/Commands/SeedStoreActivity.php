<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;

class SeedStoreActivity extends Command
{
    protected $signature = 'activity:seed {--store= : ID de la tienda (omitir para selección interactiva)}';

    protected $description = 'Genera 30 días de actividad simulada (visitas QR + búsquedas de productos) para una tienda';

    public function handle(): int
    {
        $store = $this->resolveStore();

        if (! $store) {
            $this->components->error('Tienda no encontrada.');

            return self::FAILURE;
        }

        $branches = $store->branches()->get();

        if ($branches->isEmpty()) {
            $this->components->error("La tienda \"{$store->name}\" no tiene sucursales.");

            return self::FAILURE;
        }

        $products = $store->products()->whereNotNull('barcode')->get(['id', 'name', 'barcode']);

        if ($products->isEmpty()) {
            $this->components->warn("La tienda \"{$store->name}\" no tiene productos con código de barras. Solo se generarán visitas.");
        }

        info("Generando 30 días de actividad para \"{$store->name}\"…");

        $pvTotal = 0;
        $psTotal = 0;

        foreach ($branches as $branch) {
            [$pv, $ps] = $this->seedBranch($branch, $products->toArray());
            $pvTotal += $pv;
            $psTotal += $ps;
        }

        table(
            ['Métrica', 'Cantidad'],
            [
                ['Sucursales procesadas', $branches->count()],
                ['Visitas QR generadas', $pvTotal],
                ['Búsquedas generadas', $psTotal],
            ]
        );

        $this->components->info("Actividad generada correctamente para \"{$store->name}\".");

        return self::SUCCESS;
    }

    private function resolveStore(): ?Store
    {
        if ($this->option('store')) {
            return Store::find((int) $this->option('store'));
        }

        $stores = Store::orderBy('name')->get(['id', 'name']);

        if ($stores->isEmpty()) {
            $this->components->error('No hay tiendas registradas.');

            return null;
        }

        $storeId = select(
            label: '¿Para qué tienda generás la actividad?',
            options: $stores->pluck('name', 'id')->all(),
        );

        return Store::find($storeId);
    }

    /**
     * @param  array<int, mixed>  $products
     * @return array{int, int}
     */
    private function seedBranch(Branch $branch, array $products): array
    {
        $path = '/v/'.$branch->qr_token;

        // Construir pool ponderado: más peso a los primeros productos
        $pool = [];
        $weight = count($products);
        foreach ($products as $product) {
            $w = max(1, (int) ($weight * 0.6));
            for ($i = 0; $i < $w; $i++) {
                $pool[] = ['id' => $product['id'], 'barcode' => $product['barcode']];
            }
            $weight--;
        }

        $unknownBarcodes = [
            '7798912000001', '7792222001234', '7500435011234',
            '9900001234567', '7796520000001', '4006381359429',
            '8410400052302', '7891991010948',
        ];

        $pvRows = [];
        $psRows = [];

        for ($d = 29; $d >= 0; $d--) {
            $date = Carbon::now('America/Argentina/Buenos_Aires')->subDays($d);
            $isWeekend = $date->isWeekend();

            $pvCount = $isWeekend ? rand(12, 28) : rand(22, 58);
            for ($v = 0; $v < $pvCount; $v++) {
                $ts = $this->randomTimestamp($date);
                $pvRows[] = [
                    'path' => $path,
                    'ip_hash' => hash('sha256', rand(10000, 99999).'.'.$d.'.'.$v),
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }

            if (empty($pool)) {
                continue;
            }

            $psCount = $isWeekend ? rand(8, 18) : rand(14, 35);
            for ($s = 0; $s < $psCount; $s++) {
                $ts = $this->randomTimestamp($date);
                $notFound = rand(1, 100) <= 18;

                if ($notFound) {
                    $bc = $unknownBarcodes[array_rand($unknownBarcodes)];
                    $psRows[] = ['branch_id' => $branch->id, 'product_id' => null, 'barcode' => $bc, 'found' => 0, 'created_at' => $ts, 'updated_at' => $ts];
                } else {
                    $item = $pool[array_rand($pool)];
                    $psRows[] = ['branch_id' => $branch->id, 'product_id' => $item['id'], 'barcode' => $item['barcode'], 'found' => 1, 'created_at' => $ts, 'updated_at' => $ts];
                }
            }
        }

        foreach (array_chunk($pvRows, 500) as $chunk) {
            DB::table('page_views')->insert($chunk);
        }

        foreach (array_chunk($psRows, 500) as $chunk) {
            DB::table('product_searches')->insert($chunk);
        }

        return [count($pvRows), count($psRows)];
    }

    private function randomTimestamp(Carbon $date): string
    {
        $r = rand(1, 100);
        $hour = match (true) {
            $r <= 30 => rand(10, 13), // pico mañana
            $r <= 65 => rand(17, 21), // pico tarde-noche
            $r <= 82 => rand(14, 16), // mediodía
            default => rand(9, 22),
        };

        return $date->copy()->setTime($hour, rand(0, 59), rand(0, 59))->utc()->toDateTimeString();
    }
}
