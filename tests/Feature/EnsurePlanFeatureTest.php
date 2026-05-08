<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EnsurePlanFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function createUserWithPlan(array $planFeatures, string $status = 'active'): User
    {
        $plan = Plan::create(array_merge([
            'name' => 'Test Plan',
            'price_usd' => 0,
            'price_ars' => 0,
        ], $planFeatures));

        $store = Store::create(['name' => 'Test Store', 'slug' => 'test-'.uniqid()]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'trial_ends_at' => $status === 'trial' ? now()->addDays(14) : now()->subDay(),
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ]);

        return User::factory()->create([
            'role' => 'owner',
            'store_id' => $store->id,
        ]);
    }

    public function test_trial_user_can_access_feature_protected_route(): void
    {
        $user = $this->createUserWithPlan(['has_price_lists' => false], 'trial');

        $this->actingAs($user)
            ->get(route('dashboard.price-lists.index'))
            ->assertOk();
    }

    public function test_active_user_with_feature_can_access_route(): void
    {
        $user = $this->createUserWithPlan(['has_price_lists' => true]);

        $this->actingAs($user)
            ->get(route('dashboard.price-lists.index'))
            ->assertOk();
    }

    public function test_active_user_without_feature_is_redirected(): void
    {
        $user = $this->createUserWithPlan(['has_price_lists' => false]);

        $this->actingAs($user)
            ->get(route('dashboard.price-lists.index'))
            ->assertRedirect(route('dashboard.subscription'))
            ->assertSessionHas('feature_blocked', 'has_price_lists');
    }

    public function test_impersonating_session_bypasses_feature_check(): void
    {
        // When an admin impersonates an owner without the feature, access must still be granted.
        $user = $this->createUserWithPlan(['has_price_lists' => false]);

        $this->actingAs($user)
            ->withSession(['impersonating_admin_id' => 1])
            ->get(route('dashboard.price-lists.index'))
            ->assertOk();
    }

    public function test_blocked_feature_access_is_logged_in_activity_log(): void
    {
        $user = $this->createUserWithPlan(['has_price_lists' => false]);

        $this->actingAs($user)
            ->get(route('dashboard.price-lists.index'));

        $this->assertDatabaseHas('activity_log', [
            'event_type' => 'feature.blocked',
        ]);

        $log = ActivityLog::where('event_type', 'feature.blocked')->first();
        $this->assertEquals('has_price_lists', $log->properties['feature']);
    }
}
