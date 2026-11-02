<?php

namespace Tests\Feature;

use App\Models\OdooConnection;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Odoo\OdooException;
use App\Services\Odoo\OdooProductSyncService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OdooIntegrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function createOwner(array $planAttributes = []): User
    {
        $plan = Plan::create(array_merge([
            'name' => 'Pro',
            'price_usd' => 10,
            'price_ars' => 10000,
            'has_api' => true,
        ], $planAttributes));

        $store = Store::create(['name' => 'Test Store', 'slug' => 'test-'.uniqid()]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        return User::factory()->create(['role' => 'owner', 'store_id' => $store->id]);
    }

    private function createConnection(Store $store, array $attributes = []): OdooConnection
    {
        return OdooConnection::create(array_merge([
            'store_id' => $store->id,
            'url' => 'https://miempresa.odoo.com',
            'database' => 'miempresa',
            'api_key' => 'secret-key-1234',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function odooProduct(int $id, string $barcode, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'barcode' => $barcode,
            'name' => "Producto {$id}",
            'list_price' => 1000.5,
            'active' => true,
            'write_date' => '2026-11-01 10:00:00',
        ], $overrides);
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    private function fakeOdoo(array $records, int $count = 10): void
    {
        Http::fake([
            '*/json/2/product.product/search_count' => Http::response($count),
            '*/json/2/product.product/search_read' => Http::response($records),
        ]);
    }

    // ── Conexión ─────────────────────────────────────────────────────

    public function test_owner_connects_odoo_online_and_first_sync_runs(): void
    {
        $user = $this->createOwner();
        $this->fakeOdoo([$this->odooProduct(1, '7790001')], 42);

        $this->actingAs($user)
            ->put(route('dashboard.settings.odoo.update'), [
                'url' => 'miempresa.odoo.com/odoo/action-123',
                'api_key' => 'secret-key-1234',
            ])
            ->assertRedirect(route('dashboard.settings.odoo'))
            ->assertSessionHas('success');

        $connection = OdooConnection::firstOrFail();
        $this->assertSame('https://miempresa.odoo.com', $connection->url);
        $this->assertSame('miempresa', $connection->database);
        $this->assertSame('secret-key-1234', $connection->api_key);
        $this->assertNotSame('secret-key-1234', $connection->getRawOriginal('api_key'));

        Http::assertSent(fn (Request $request) => $request->url() === 'https://miempresa.odoo.com/json/2/product.product/search_count'
            && $request->hasHeader('Authorization', 'bearer secret-key-1234')
            && $request->hasHeader('X-Odoo-Database', 'miempresa'));

        $this->assertDatabaseHas('products', [
            'store_id' => $user->store_id,
            'barcode' => '7790001',
            'source' => 'odoo',
            'odoo_product_id' => 1,
        ]);
    }

    public function test_invalid_api_key_is_not_saved(): void
    {
        $user = $this->createOwner();
        Http::fake(['*' => Http::response(['name' => 'AccessDenied', 'message' => 'Access Denied'], 401)]);

        $this->actingAs($user)
            ->put(route('dashboard.settings.odoo.update'), [
                'url' => 'https://miempresa.odoo.com',
                'api_key' => 'wrong',
            ])
            ->assertSessionHasErrors('connection');

        $this->assertDatabaseCount('odoo_connections', 0);
    }

    public function test_custom_domain_requires_database(): void
    {
        $user = $this->createOwner();
        Http::fake();

        $this->actingAs($user)
            ->put(route('dashboard.settings.odoo.update'), [
                'url' => 'https://erp.example.com',
                'api_key' => 'secret',
            ])
            ->assertSessionHasErrors('database');

        Http::assertNothingSent();
    }

    public function test_private_or_non_https_urls_are_rejected(): void
    {
        $user = $this->createOwner();
        Http::fake();

        foreach (['https://127.0.0.1', 'https://169.254.169.254', 'http://miempresa.odoo.com', 'https://localhost'] as $url) {
            $this->actingAs($user)
                ->put(route('dashboard.settings.odoo.update'), ['url' => $url, 'database' => 'db', 'api_key' => 'k'])
                ->assertSessionHasErrors('url');
        }

        Http::assertNothingSent();
    }

    public function test_updating_without_api_key_keeps_the_stored_one(): void
    {
        $user = $this->createOwner();
        $connection = $this->createConnection($user->store, ['sync_cursor' => '2026-11-01 10:00:00']);
        $this->fakeOdoo([]);

        $this->actingAs($user)
            ->put(route('dashboard.settings.odoo.update'), ['url' => 'https://miempresa.odoo.com', 'api_key' => ''])
            ->assertSessionHasNoErrors();

        $connection->refresh();
        $this->assertSame('secret-key-1234', $connection->api_key);
        $this->assertSame('2026-11-01 10:00:00', $connection->sync_cursor);
    }

    public function test_plan_without_api_feature_cannot_access_integration(): void
    {
        $user = $this->createOwner(['has_api' => false]);

        $this->actingAs($user)
            ->get(route('dashboard.settings.odoo'))
            ->assertRedirect(route('dashboard.subscription'));
    }

    public function test_settings_page_renders_connection_status(): void
    {
        $user = $this->createOwner();
        $this->createConnection($user->store, [
            'last_sync_status' => 'failed',
            'last_error' => 'Odoo rechazó la API key',
            'last_synced_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.settings.odoo'))
            ->assertOk()
            ->assertSee('Conectado a Odoo')
            ->assertSee('Odoo rechazó la API key')
            ->assertDontSee('secret-key-1234');
    }

    public function test_disconnect_keeps_products_and_makes_them_editable(): void
    {
        $user = $this->createOwner();
        $this->createConnection($user->store);
        $product = Product::create(['store_id' => $user->store_id, 'name' => 'A', 'barcode' => '1', 'source' => 'odoo', 'odoo_product_id' => 5]);

        $this->actingAs($user)
            ->delete(route('dashboard.settings.odoo.destroy'))
            ->assertRedirect(route('dashboard.settings.odoo'));

        $this->assertDatabaseCount('odoo_connections', 0);
        $this->assertNull($product->fresh()->source);
    }

    // ── Sincronización ───────────────────────────────────────────────

    public function test_sync_creates_updates_and_deactivates_products(): void
    {
        $user = $this->createOwner();
        $store = $user->store;
        $connection = $this->createConnection($store);

        $manual = Product::create(['store_id' => $store->id, 'name' => 'Viejo', 'barcode' => '200', 'price' => 10, 'active' => true]);
        $toArchive = Product::create(['store_id' => $store->id, 'name' => 'Archivar', 'barcode' => '300', 'active' => true, 'source' => 'odoo', 'odoo_product_id' => 3]);

        $this->fakeOdoo([
            $this->odooProduct(1, '100', ['name' => 'Nuevo', 'list_price' => 1250.5]),
            $this->odooProduct(2, '200', ['name' => 'Actualizado', 'list_price' => 99, 'write_date' => '2026-11-01 12:00:00']),
            $this->odooProduct(3, '300', ['active' => false]),
            $this->odooProduct(4, '400', ['active' => false]),
            $this->odooProduct(5, '500', ['list_price' => 0]),
        ]);

        $stats = app(OdooProductSyncService::class)->sync($connection);

        $this->assertSame(2, $stats['created']);
        $this->assertSame(1, $stats['updated']);
        $this->assertSame(1, $stats['deactivated']);

        $this->assertDatabaseHas('products', ['store_id' => $store->id, 'barcode' => '100', 'name' => 'Nuevo', 'price' => 1250.5, 'source' => 'odoo']);
        $this->assertDatabaseHas('products', ['store_id' => $store->id, 'barcode' => '500', 'price' => null]);
        $this->assertDatabaseMissing('products', ['barcode' => '400']);

        $manual->refresh();
        $this->assertSame('Actualizado', $manual->name);
        $this->assertSame('odoo', $manual->source);
        $this->assertSame(2, $manual->odoo_product_id);
        $this->assertFalse($toArchive->fresh()->active);

        $connection->refresh();
        $this->assertSame('success', $connection->last_sync_status);
        $this->assertSame('2026-11-01 12:00:00', $connection->sync_cursor);
        $this->assertNotNull($connection->last_synced_at);
    }

    public function test_sync_requests_archived_products_and_uses_cursor(): void
    {
        $user = $this->createOwner();
        $connection = $this->createConnection($user->store, ['sync_cursor' => '2026-10-31 08:00:00']);
        $this->fakeOdoo([]);

        app(OdooProductSyncService::class)->sync($connection);

        Http::assertSent(function (Request $request) {
            return str_ends_with($request->url(), '/json/2/product.product/search_read')
                && $request['context'] === ['active_test' => false]
                && in_array(['write_date', '>=', '2026-10-31 08:00:00'], $request['domain'], true)
                && $request['order'] === 'write_date asc, id asc';
        });

        $this->assertSame('2026-10-31 08:00:00', $connection->fresh()->sync_cursor);
    }

    public function test_sync_paginates_until_last_page(): void
    {
        config(['services.odoo.page_size' => 2]);
        $user = $this->createOwner();
        $connection = $this->createConnection($user->store);

        Http::fakeSequence('*/json/2/product.product/search_read')
            ->push([$this->odooProduct(1, '1'), $this->odooProduct(2, '2')])
            ->push([$this->odooProduct(3, '3')]);

        $stats = app(OdooProductSyncService::class)->sync($connection);

        $this->assertSame(3, $stats['created']);
        Http::assertSentCount(2);
    }

    public function test_sync_respects_plan_product_limit(): void
    {
        $user = $this->createOwner(['max_products' => 2]);
        $connection = $this->createConnection($user->store);
        Product::create(['store_id' => $user->store_id, 'name' => 'Existente', 'barcode' => '999', 'active' => true]);

        $this->fakeOdoo([
            $this->odooProduct(1, '1'),
            $this->odooProduct(2, '2'),
            $this->odooProduct(3, '3'),
        ]);

        $stats = app(OdooProductSyncService::class)->sync($connection);

        $this->assertSame(1, $stats['created']);
        $this->assertSame(2, $stats['skipped_limit']);
        $this->assertSame(2, Product::where('store_id', $user->store_id)->count());
    }

    public function test_sync_skips_barcode_conflicts(): void
    {
        $user = $this->createOwner();
        $connection = $this->createConnection($user->store);
        Product::create(['store_id' => $user->store_id, 'name' => 'De Odoo', 'barcode' => '111', 'source' => 'odoo', 'odoo_product_id' => 7]);
        Product::create(['store_id' => $user->store_id, 'name' => 'Manual', 'barcode' => '222']);

        $this->fakeOdoo([$this->odooProduct(7, '222')]);

        $stats = app(OdooProductSyncService::class)->sync($connection);

        $this->assertSame(1, $stats['skipped_conflict']);
        $this->assertDatabaseHas('products', ['odoo_product_id' => 7, 'barcode' => '111']);
    }

    public function test_sync_failure_is_recorded_on_connection(): void
    {
        $user = $this->createOwner();
        $connection = $this->createConnection($user->store);
        Http::fake(['*' => Http::response(['message' => 'Access Denied'], 401)]);

        try {
            app(OdooProductSyncService::class)->sync($connection);
            $this->fail('Se esperaba OdooException');
        } catch (OdooException $e) {
            $this->assertStringContainsString('API key', $e->getMessage());
        }

        $connection->refresh();
        $this->assertSame('failed', $connection->last_sync_status);
        $this->assertStringContainsString('API key', $connection->last_error);
    }

    public function test_command_only_syncs_stores_with_api_feature(): void
    {
        $withFeature = $this->createOwner();
        $withoutFeature = $this->createOwner(['has_api' => false]);
        $this->createConnection($withFeature->store);
        $this->createConnection($withoutFeature->store);
        $this->fakeOdoo([$this->odooProduct(1, '100')]);

        $this->artisan('odoo:sync')->assertSuccessful();

        $this->assertSame(1, Product::where('store_id', $withFeature->store_id)->count());
        $this->assertSame(0, Product::where('store_id', $withoutFeature->store_id)->count());
    }

    // ── Productos gestionados por Odoo ───────────────────────────────

    public function test_odoo_products_keep_synced_fields_when_edited(): void
    {
        $user = $this->createOwner();
        $product = Product::create([
            'store_id' => $user->store_id, 'name' => 'Leche', 'barcode' => '779', 'price' => 100,
            'active' => true, 'source' => 'odoo', 'odoo_product_id' => 1,
        ]);

        $this->actingAs($user)
            ->put(route('dashboard.products.update', $product), [
                'name' => 'Otro nombre',
                'barcode' => '000',
                'price' => 1,
                'active' => 0,
                'description' => 'Descripción local',
            ])
            ->assertRedirect(route('dashboard.products.index'));

        $product->refresh();
        $this->assertSame('Leche', $product->name);
        $this->assertSame('779', $product->barcode);
        $this->assertEquals(100, $product->price);
        $this->assertTrue($product->active);
        $this->assertSame('Descripción local', $product->description);
    }

    public function test_manual_products_remain_fully_editable(): void
    {
        $user = $this->createOwner();
        $product = Product::create(['store_id' => $user->store_id, 'name' => 'Leche', 'barcode' => '779', 'price' => 100, 'active' => true]);

        $this->actingAs($user)
            ->put(route('dashboard.products.update', $product), ['name' => 'Leche entera', 'barcode' => '779', 'price' => 150, 'active' => 1])
            ->assertRedirect(route('dashboard.products.index'));

        $this->assertSame('Leche entera', $product->fresh()->name);
        $this->assertEquals(150, $product->fresh()->price);
    }
}
