<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BranchPromoImageTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    private Store $store;

    private Branch $branch;

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

        $this->branch = Branch::create([
            'store_id' => $this->store->id,
            'name' => 'Sucursal Principal',
        ]);
    }

    // ── update: promo image upload ────────────────────────────────

    public function test_update_stores_promo_image_and_timing(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('promo.jpg', 800, 600);

        $this->actingAs($this->user)
            ->put(route('dashboard.branches.update', $this->branch), [
                'name' => $this->branch->name,
                'promo_image' => $file,
                'promo_show_when' => 'on_load',
            ])
            ->assertRedirect(route('dashboard.branches.index'));

        $this->branch->refresh();

        $this->assertNotNull($this->branch->promo_image_path);
        $this->assertSame('on_load', $this->branch->promo_show_when);
        Storage::disk('public')->assertExists($this->branch->promo_image_path);
    }

    public function test_update_with_empty_promo_show_when_stores_null(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('dashboard.branches.update', $this->branch), [
                'name' => $this->branch->name,
                'promo_show_when' => '',
            ])
            ->assertRedirect(route('dashboard.branches.index'));

        $this->branch->refresh();
        $this->assertNull($this->branch->promo_show_when);
    }

    public function test_update_rejects_invalid_promo_show_when(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->put(route('dashboard.branches.update', $this->branch), [
                'name' => $this->branch->name,
                'promo_show_when' => 'invalid_value',
            ])
            ->assertSessionHasErrors('promo_show_when');
    }

    public function test_update_rejects_oversized_promo_image(): void
    {
        Storage::fake('public');

        $bigFile = UploadedFile::fake()->image('big.jpg')->size(4000);

        $this->actingAs($this->user)
            ->put(route('dashboard.branches.update', $this->branch), [
                'name' => $this->branch->name,
                'promo_image' => $bigFile,
            ])
            ->assertSessionHasErrors('promo_image');
    }

    public function test_update_replaces_existing_promo_image(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store("promos/{$this->branch->id}", 'public');
        $this->branch->update(['promo_image_path' => $oldPath, 'promo_show_when' => 'on_load']);

        $newFile = UploadedFile::fake()->image('new.jpg', 600, 400);

        $this->actingAs($this->user)
            ->put(route('dashboard.branches.update', $this->branch), [
                'name' => $this->branch->name,
                'promo_image' => $newFile,
            ])
            ->assertRedirect(route('dashboard.branches.index'));

        Storage::disk('public')->assertMissing($oldPath);
        $this->branch->refresh();
        Storage::disk('public')->assertExists($this->branch->promo_image_path);
    }

    // ── destroyPromoImage ─────────────────────────────────────────

    public function test_destroy_promo_image_deletes_file_and_nullifies_columns(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('promo.jpg');
        $path = $file->store("promos/{$this->branch->id}", 'public');
        $this->branch->update(['promo_image_path' => $path, 'promo_show_when' => 'after_first_scan']);

        $this->actingAs($this->user)
            ->delete(route('dashboard.branches.promo-image.destroy', $this->branch))
            ->assertRedirect(route('dashboard.branches.edit', $this->branch));

        Storage::disk('public')->assertMissing($path);
        $this->branch->refresh();
        $this->assertNull($this->branch->promo_image_path);
        $this->assertNull($this->branch->promo_show_when);
    }

    public function test_destroy_promo_image_is_noop_when_no_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->delete(route('dashboard.branches.promo-image.destroy', $this->branch))
            ->assertRedirect(route('dashboard.branches.edit', $this->branch));

        $this->branch->refresh();
        $this->assertNull($this->branch->promo_image_path);
    }

    public function test_destroy_promo_image_is_forbidden_for_other_store(): void
    {
        Storage::fake('public');

        $plan = Plan::first();

        $otherStore = Store::create(['name' => 'Other', 'slug' => 'other-'.uniqid()]);
        Subscription::create([
            'store_id' => $otherStore->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $otherUser = User::factory()->create(['role' => 'owner', 'store_id' => $otherStore->id]);

        $this->actingAs($otherUser)
            ->delete(route('dashboard.branches.promo-image.destroy', $this->branch))
            ->assertForbidden();
    }

    // ── ScanViewController ────────────────────────────────────────

    public function test_scan_view_passes_promo_data_when_configured(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('promo.jpg');
        $path = $file->store("promos/{$this->branch->id}", 'public');
        $this->branch->update(['promo_image_path' => $path, 'promo_show_when' => 'on_load']);

        $this->get(route('scan.index', ['token' => $this->branch->qr_token]))
            ->assertOk()
            ->assertViewHas('promoShowWhen', 'on_load')
            ->assertViewHas('promoDataUri', fn ($value) => $value !== null);
    }

    public function test_scan_view_passes_null_when_promo_show_when_is_null(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('promo.jpg');
        $path = $file->store("promos/{$this->branch->id}", 'public');
        $this->branch->update(['promo_image_path' => $path, 'promo_show_when' => null]);

        $this->get(route('scan.index', ['token' => $this->branch->qr_token]))
            ->assertOk()
            ->assertViewHas('promoDataUri', null)
            ->assertViewHas('promoShowWhen', null);
    }

    public function test_scan_view_passes_null_when_promo_file_missing(): void
    {
        Storage::fake('public');

        $this->branch->update([
            'promo_image_path' => 'promos/999/nonexistent.jpg',
            'promo_show_when' => 'on_load',
        ]);

        $this->get(route('scan.index', ['token' => $this->branch->qr_token]))
            ->assertOk()
            ->assertViewHas('promoDataUri', null)
            ->assertViewHas('promoShowWhen', null);
    }
}
