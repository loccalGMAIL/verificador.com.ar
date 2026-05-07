<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductCustomFieldDefinition;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CustomFieldTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name'      => 'Test Plan',
            'price_usd' => 0,
            'price_ars' => 0,
        ]);

        $this->store = Store::create([
            'name' => 'Test Store',
            'slug' => 'test-store-'.uniqid(),
        ]);

        Subscription::create([
            'store_id'      => $this->store->id,
            'plan_id'       => $plan->id,
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'starts_at'     => now(),
            'ends_at'       => now()->addDays(30),
        ]);

        $this->user = User::factory()->create([
            'role'     => 'owner',
            'store_id' => $this->store->id,
        ]);
    }

    // ── index ──────────────────────────────────────────────────

    public function test_index_is_accessible(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard.settings.custom-fields.index'))
            ->assertOk()
            ->assertViewIs('dashboard.settings.custom-fields');
    }

    public function test_index_lists_existing_fields(): void
    {
        ProductCustomFieldDefinition::create([
            'store_id'        => $this->store->id,
            'label'           => 'Marca',
            'excel_column'    => 'marca',
            'visible_on_scan' => true,
        ]);

        $this->actingAs($this->user)
            ->get(route('dashboard.settings.custom-fields.index'))
            ->assertOk()
            ->assertSee('Marca');
    }

    // ── store ──────────────────────────────────────────────────

    public function test_can_create_custom_field(): void
    {
        $this->actingAs($this->user)
            ->post(route('dashboard.settings.custom-fields.store'), [
                'label'           => 'Marca',
                'excel_column'    => 'marca',
                'visible_on_scan' => '1',
            ])
            ->assertRedirect(route('dashboard.settings.custom-fields.index'));

        $this->assertDatabaseHas('product_custom_field_definitions', [
            'store_id'        => $this->store->id,
            'label'           => 'Marca',
            'excel_column'    => 'marca',
            'visible_on_scan' => true,
        ]);
    }

    public function test_cannot_create_duplicate_excel_column(): void
    {
        ProductCustomFieldDefinition::create([
            'store_id'     => $this->store->id,
            'label'        => 'Marca',
            'excel_column' => 'marca',
        ]);

        $this->actingAs($this->user)
            ->post(route('dashboard.settings.custom-fields.store'), [
                'label'        => 'Marca 2',
                'excel_column' => 'marca',
            ])
            ->assertSessionHasErrors('excel_column');
    }

    public function test_store_requires_label_and_column(): void
    {
        $this->actingAs($this->user)
            ->post(route('dashboard.settings.custom-fields.store'), [])
            ->assertSessionHasErrors(['label', 'excel_column']);
    }

    // ── update ─────────────────────────────────────────────────

    public function test_can_update_custom_field(): void
    {
        $field = ProductCustomFieldDefinition::create([
            'store_id'        => $this->store->id,
            'label'           => 'Marca',
            'excel_column'    => 'marca',
            'visible_on_scan' => true,
        ]);

        $this->actingAs($this->user)
            ->put(route('dashboard.settings.custom-fields.update', $field), [
                'label'           => 'Marca comercial',
                'excel_column'    => 'marca',
                'visible_on_scan' => '0',
            ])
            ->assertRedirect(route('dashboard.settings.custom-fields.index'));

        $this->assertDatabaseHas('product_custom_field_definitions', [
            'id'              => $field->id,
            'label'           => 'Marca comercial',
            'visible_on_scan' => false,
        ]);
    }

    public function test_cannot_update_field_of_another_store(): void
    {
        $otherStore = Store::create(['name' => 'Other', 'slug' => 'other-'.uniqid()]);
        $field = ProductCustomFieldDefinition::create([
            'store_id'     => $otherStore->id,
            'label'        => 'Origen',
            'excel_column' => 'origen',
        ]);

        $this->actingAs($this->user)
            ->put(route('dashboard.settings.custom-fields.update', $field), [
                'label'        => 'Hacked',
                'excel_column' => 'origen',
            ])
            ->assertForbidden();
    }

    // ── destroy ────────────────────────────────────────────────

    public function test_can_delete_custom_field(): void
    {
        $field = ProductCustomFieldDefinition::create([
            'store_id'     => $this->store->id,
            'label'        => 'Peso',
            'excel_column' => 'peso',
        ]);

        $this->actingAs($this->user)
            ->delete(route('dashboard.settings.custom-fields.destroy', $field))
            ->assertRedirect(route('dashboard.settings.custom-fields.index'));

        $this->assertModelMissing($field);
    }

    public function test_cannot_delete_field_of_another_store(): void
    {
        $otherStore = Store::create(['name' => 'Other', 'slug' => 'other-'.uniqid()]);
        $field = ProductCustomFieldDefinition::create([
            'store_id'     => $otherStore->id,
            'label'        => 'Peso',
            'excel_column' => 'peso',
        ]);

        $this->actingAs($this->user)
            ->delete(route('dashboard.settings.custom-fields.destroy', $field))
            ->assertForbidden();
    }

    // ── Scan API ───────────────────────────────────────────────

    public function test_scan_api_returns_visible_custom_fields(): void
    {
        ProductCustomFieldDefinition::create([
            'store_id'        => $this->store->id,
            'label'           => 'Marca',
            'excel_column'    => 'marca',
            'visible_on_scan' => true,
            'sort_order'      => 0,
        ]);
        ProductCustomFieldDefinition::create([
            'store_id'        => $this->store->id,
            'label'           => 'Origen',
            'excel_column'    => 'origen',
            'visible_on_scan' => false,
            'sort_order'      => 1,
        ]);

        $branch = Branch::create([
            'store_id'  => $this->store->id,
            'name'      => 'Sucursal Test',
            'qr_token'  => 'test-token-'.uniqid(),
            'active'    => true,
        ]);

        $product = Product::create([
            'store_id'      => $this->store->id,
            'name'          => 'Leche',
            'barcode'       => '7790001234567',
            'price'         => 1250.50,
            'active'        => true,
            'custom_fields' => ['marca' => 'La Serenísima', 'origen' => 'Argentina'],
        ]);

        $this->getJson("/api/scan/{$branch->qr_token}/{$product->barcode}")
            ->assertOk()
            ->assertJsonPath('found', true)
            ->assertJsonPath('custom_fields.0.label', 'Marca')
            ->assertJsonPath('custom_fields.0.value', 'La Serenísima')
            ->assertJsonMissingPath('custom_fields.1');
    }

    public function test_scan_api_omits_custom_fields_when_empty(): void
    {
        $branch = Branch::create([
            'store_id' => $this->store->id,
            'name'     => 'Sucursal Test',
            'qr_token' => 'test-token-'.uniqid(),
            'active'   => true,
        ]);

        $product = Product::create([
            'store_id' => $this->store->id,
            'name'     => 'Aceite',
            'barcode'  => '7790009876543',
            'price'    => 980.00,
            'active'   => true,
        ]);

        $this->getJson("/api/scan/{$branch->qr_token}/{$product->barcode}")
            ->assertOk()
            ->assertJsonMissingPath('custom_fields');
    }
}
