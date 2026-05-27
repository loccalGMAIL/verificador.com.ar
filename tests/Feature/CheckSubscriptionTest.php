<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CheckSubscriptionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function createOwner(string $status, ?Carbon $trialEndsAt = null): User
    {
        $plan = Plan::create(['name' => 'Basic', 'price_usd' => 5, 'price_ars' => 5000]);
        $store = Store::create(['name' => 'Test Store', 'slug' => 'test-'.uniqid()]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => now()->subDays(5),
        ]);

        return User::factory()->create(['role' => 'owner', 'store_id' => $store->id]);
    }

    public function test_user_with_active_trial_can_access_dashboard(): void
    {
        $user = $this->createOwner('trial', now()->addDays(7));

        $this->actingAs($user)
            ->get(route('dashboard.home'))
            ->assertOk();
    }

    public function test_user_with_active_subscription_can_access_dashboard(): void
    {
        $user = $this->createOwner('active');

        $this->actingAs($user)
            ->get(route('dashboard.home'))
            ->assertOk();
    }

    public function test_user_with_expired_trial_is_blocked(): void
    {
        $user = $this->createOwner('trial', now()->subDay());

        $this->actingAs($user)
            ->get(route('dashboard.home'))
            ->assertRedirect(route('dashboard.subscription'))
            ->assertSessionHas('subscription_expired', true);
    }

    public function test_user_with_suspended_subscription_is_blocked(): void
    {
        $user = $this->createOwner('suspended');

        $this->actingAs($user)
            ->get(route('dashboard.home'))
            ->assertRedirect(route('dashboard.subscription'))
            ->assertSessionHas('subscription_expired', true);
    }

    public function test_user_with_cancelled_subscription_is_blocked(): void
    {
        $user = $this->createOwner('cancelled');

        $this->actingAs($user)
            ->get(route('dashboard.home'))
            ->assertRedirect(route('dashboard.subscription'))
            ->assertSessionHas('subscription_expired', true);
    }

    public function test_user_without_subscription_is_blocked(): void
    {
        $store = Store::create(['name' => 'No Sub Store', 'slug' => 'nosub-'.uniqid()]);
        $user = User::factory()->create(['role' => 'owner', 'store_id' => $store->id]);

        $this->actingAs($user)
            ->get(route('dashboard.home'))
            ->assertRedirect(route('dashboard.subscription'))
            ->assertSessionHas('subscription_expired', true);
    }

    public function test_expired_trial_user_can_access_subscription_page(): void
    {
        $user = $this->createOwner('trial', now()->subDay());

        $this->actingAs($user)
            ->get(route('dashboard.subscription'))
            ->assertOk();
    }

    public function test_expired_trial_user_can_access_billing_page(): void
    {
        $user = $this->createOwner('trial', now()->subDay());

        $this->actingAs($user)
            ->get(route('dashboard.billing'))
            ->assertOk();
    }

    public function test_impersonating_session_does_not_bypass_subscription_check(): void
    {
        $user = $this->createOwner('trial', now()->subDay());

        $this->actingAs($user)
            ->withSession(['impersonating_admin_id' => 1])
            ->get(route('dashboard.home'))
            ->assertRedirect(route('dashboard.subscription'))
            ->assertSessionHas('subscription_expired', true);
    }

}
