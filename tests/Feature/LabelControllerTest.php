<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Product;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LabelControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name' => 'Test Plan',
            'price_usd' => 0,
            'price_ars' => 0,
        ]);

        $this->store = Store::create([
            'name' => 'Test Store',
            'slug' => 'test-store-'.uniqid(),
        ]);

        Subscription::create([
            'store_id' => $this->store->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->user = User::factory()->create([
            'role' => 'owner',
            'store_id' => $this->store->id,
        ]);
    }

    // ── index ──────────────────────────────────────────────────

    public function test_index_is_accessible(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.labels.index'))
            ->assertOk()
            ->assertViewIs('dashboard.labels.index');
    }

    public function test_index_requires_authentication(): void
    {
        $this->get(route('dashboard.labels.index'))->assertRedirect(route('login'));
    }

    // ── generate ───────────────────────────────────────────────

    public function test_generate_returns_seven_digit_barcode(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.generate'));

        $response->assertOk()->assertJsonStructure(['barcode']);
        $this->assertMatchesRegularExpression('/^\d{7}$/', $response->json('barcode'));
    }

    public function test_generate_avoids_existing_barcodes(): void
    {
        $first = $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.generate'))->json('barcode');

        Product::create(['store_id' => $this->store->id, 'name' => 'P', 'barcode' => $first]);

        $second = $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.generate'))->json('barcode');

        $this->assertNotEquals($first, $second);
    }

    // ── check ─────────────────────────────────────────────────

    public function test_check_returns_not_exists_for_free_barcode(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.check'), ['barcode' => '9999999'])
            ->assertOk()
            ->assertJson(['exists' => false]);
    }

    public function test_check_returns_exists_for_taken_barcode(): void
    {
        Product::create(['store_id' => $this->store->id, 'name' => 'Artesanía A', 'barcode' => '1234567']);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.check'), ['barcode' => '1234567'])
            ->assertOk()
            ->assertJson(['exists' => true, 'product_name' => 'Artesanía A']);
    }

    public function test_check_excludes_current_product_from_search(): void
    {
        $product = Product::create(['store_id' => $this->store->id, 'name' => 'P', 'barcode' => '1234567']);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.check'), [
                'barcode' => '1234567',
                'product_id' => $product->id,
            ])
            ->assertOk()
            ->assertJson(['exists' => false]);
    }

    public function test_check_does_not_see_other_stores_barcodes(): void
    {
        $otherStore = Store::create(['name' => 'Other', 'slug' => 'other-'.uniqid()]);
        Product::create(['store_id' => $otherStore->id, 'name' => 'P', 'barcode' => '7654321']);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.check'), ['barcode' => '7654321'])
            ->assertOk()
            ->assertJson(['exists' => false]);
    }

    // ── assign ────────────────────────────────────────────────

    public function test_assign_saves_barcode_to_product(): void
    {
        $product = Product::create(['store_id' => $this->store->id, 'name' => 'P', 'barcode' => null]);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.assign', $product), ['barcode' => '7654321'])
            ->assertOk()
            ->assertJson(['success' => true, 'barcode' => '7654321']);

        $this->assertEquals('7654321', $product->fresh()->barcode);
    }

    public function test_assign_rejects_duplicate_barcode_in_same_store(): void
    {
        Product::create(['store_id' => $this->store->id, 'name' => 'Existing', 'barcode' => '1234567']);
        $product = Product::create(['store_id' => $this->store->id, 'name' => 'New', 'barcode' => null]);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.assign', $product), ['barcode' => '1234567'])
            ->assertStatus(422);

        $this->assertNull($product->fresh()->barcode);
    }

    public function test_assign_allows_same_barcode_in_different_store(): void
    {
        $otherStore = Store::create(['name' => 'Other', 'slug' => 'other-'.uniqid()]);
        Product::create(['store_id' => $otherStore->id, 'name' => 'P', 'barcode' => '1234567']);

        $product = Product::create(['store_id' => $this->store->id, 'name' => 'Mine', 'barcode' => null]);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.assign', $product), ['barcode' => '1234567'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_assign_forbids_product_from_other_store(): void
    {
        $otherStore = Store::create(['name' => 'Other', 'slug' => 'other-'.uniqid()]);
        $otherProduct = Product::create(['store_id' => $otherStore->id, 'name' => 'P', 'barcode' => null]);

        $this->actingAs($this->user)
            ->postJson(route('dashboard.labels.assign', $otherProduct), ['barcode' => '1234567'])
            ->assertForbidden();
    }

    // ── print ─────────────────────────────────────────────────

    public function test_print_returns_view_for_a4(): void
    {
        $p1 = Product::create(['store_id' => $this->store->id, 'name' => 'P1', 'barcode' => '1111111']);
        $p2 = Product::create(['store_id' => $this->store->id, 'name' => 'P2', 'barcode' => '2222222']);

        $this->actingAs($this->user)
            ->post(route('dashboard.labels.print'), [
                'product_ids' => [$p1->id, $p2->id],
                'print_mode' => 'a4',
                'columns' => 3,
                'margin_mm' => 5,
                'spacing_mm' => 3,
                'name_font_size' => 'md',
                'barcode_height' => 'md',
            ])
            ->assertOk()
            ->assertViewIs('dashboard.labels.print');
    }

    public function test_print_returns_view_for_label_printer(): void
    {
        $product = Product::create(['store_id' => $this->store->id, 'name' => 'P1', 'barcode' => '1111111']);

        $this->actingAs($this->user)
            ->post(route('dashboard.labels.print'), [
                'product_ids' => [$product->id],
                'print_mode' => 'label',
                'label_size' => '40x25',
                'name_font_size' => 'sm',
                'barcode_height' => 'sm',
            ])
            ->assertOk()
            ->assertViewIs('dashboard.labels.print');
    }

    public function test_print_requires_product_ids(): void
    {
        $this->actingAs($this->user)
            ->post(route('dashboard.labels.print'), [
                'product_ids' => [],
                'print_mode' => 'a4',
                'name_font_size' => 'md',
                'barcode_height' => 'md',
            ])
            ->assertSessionHasErrors('product_ids');
    }

    public function test_print_ignores_products_without_barcode(): void
    {
        $withBarcode = Product::create(['store_id' => $this->store->id, 'name' => 'P1', 'barcode' => '1111111']);
        $withoutBarcode = Product::create(['store_id' => $this->store->id, 'name' => 'P2', 'barcode' => null]);

        $response = $this->actingAs($this->user)
            ->post(route('dashboard.labels.print'), [
                'product_ids' => [$withBarcode->id, $withoutBarcode->id],
                'print_mode' => 'a4',
                'columns' => 3,
                'margin_mm' => 5,
                'spacing_mm' => 3,
                'name_font_size' => 'md',
                'barcode_height' => 'md',
            ])
            ->assertOk();

        $products = $response->viewData('products');
        $this->assertCount(1, $products);
        $this->assertEquals('P1', $products->first()->name);
    }

    public function test_print_repeats_labels_when_copies_is_set(): void
    {
        $p1 = Product::create(['store_id' => $this->store->id, 'name' => 'A', 'barcode' => '1111111']);
        $p2 = Product::create(['store_id' => $this->store->id, 'name' => 'B', 'barcode' => '2222222']);

        $response = $this->actingAs($this->user)
            ->post(route('dashboard.labels.print'), [
                'product_ids' => [$p1->id, $p2->id],
                'print_mode' => 'a4',
                'copies' => 3,
                'columns' => 3,
                'margin_mm' => 5,
                'spacing_mm' => 3,
                'name_font_size' => 'md',
                'barcode_height' => 'md',
            ])
            ->assertOk();

        $products = $response->viewData('products');
        $this->assertCount(6, $products);
        $this->assertEquals([$p1->id, $p1->id, $p1->id, $p2->id, $p2->id, $p2->id], $products->pluck('id')->all());
    }
}
