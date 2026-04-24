<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabelController extends Controller
{
    public function index(): View
    {
        $products = auth()->user()->store
            ->products()
            ->orderBy('name')
            ->get();

        $productsData = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'barcode' => $p->barcode,
            'active' => $p->active,
        ])->values();

        return view('dashboard.labels.index', compact('products', 'productsData'));
    }

    public function generate(): JsonResponse
    {
        $storeId = auth()->user()->store_id;

        do {
            $barcode = (string) random_int(1000000, 9999999);
        } while (Product::where('store_id', $storeId)->where('barcode', $barcode)->exists());

        return response()->json(['barcode' => $barcode]);
    }

    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:50'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $storeId = auth()->user()->store_id;

        $query = Product::where('store_id', $storeId)
            ->where('barcode', $data['barcode']);

        if (! empty($data['product_id'])) {
            $query->where('id', '!=', $data['product_id']);
        }

        $existing = $query->first(['id', 'name']);

        return response()->json([
            'exists' => $existing !== null,
            'product_name' => $existing?->name,
        ]);
    }

    public function assign(Request $request, Product $product): JsonResponse
    {
        abort_if($product->store_id !== auth()->user()->store_id, 403);

        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:50'],
        ]);

        $storeId = auth()->user()->store_id;

        $exists = Product::where('store_id', $storeId)
            ->where('barcode', $data['barcode'])
            ->where('id', '!=', $product->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Ese código ya está en uso por otro producto.'], 422);
        }

        $product->update(['barcode' => $data['barcode']]);

        return response()->json(['success' => true, 'barcode' => $data['barcode']]);
    }

    public function print(Request $request): View
    {
        $storeId = auth()->user()->store_id;

        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer'],
            'print_mode' => ['required', 'in:a4,label'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:100'],
            'columns' => ['nullable', 'integer', 'min:1', 'max:5'],
            'margin_mm' => ['nullable', 'integer', 'min:0', 'max:20'],
            'spacing_mm' => ['nullable', 'integer', 'min:0', 'max:10'],
            'label_size' => ['nullable', 'in:40x25,58x40,62x30'],
            'name_font_size' => ['required', 'in:sm,md,lg'],
            'barcode_height' => ['required', 'in:sm,md,lg'],
            'show_barcode_number' => ['nullable', 'in:0,1'],
        ]);

        $copies = (int) ($data['copies'] ?? 1);

        $products = Product::where('store_id', $storeId)
            ->whereIn('id', $data['product_ids'])
            ->whereNotNull('barcode')
            ->where('barcode', '!=', '')
            ->orderBy('name')
            ->get();

        abort_if($products->isEmpty(), 422);

        if ($copies > 1) {
            $products = $products->flatMap(fn (Product $product) => collect(range(1, $copies))->map(fn () => $product));
        }

        return view('dashboard.labels.print', [
            'products' => $products,
            'config' => $data,
        ]);
    }
}
