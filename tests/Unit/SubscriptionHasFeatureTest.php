<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SubscriptionHasFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function makePlan(array $features = []): Plan
    {
        return Plan::create(array_merge([
            'name' => 'Test Plan',
            'price_usd' => 0,
            'price_ars' => 0,
        ], $features));
    }

    private function makeSubscription(Plan $plan, array $attributes = []): Subscription
    {
        $store = Store::create(['name' => 'Test Store', 'slug' => 'test-'.uniqid()]);

        return Subscription::create(array_merge([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'trial_ends_at' => now()->subDay(),
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
        ], $attributes));
    }

    public function test_trial_subscription_returns_true_for_any_feature(): void
    {
        $plan = $this->makePlan(['has_price_lists' => false, 'has_branches' => false]);
        $sub = $this->makeSubscription($plan, [
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->assertTrue($sub->hasFeature('has_price_lists'));
        $this->assertTrue($sub->hasFeature('has_branches'));
        $this->assertTrue($sub->hasFeature('has_api'));
    }

    public function test_active_subscription_with_feature_enabled_returns_true(): void
    {
        $plan = $this->makePlan(['has_price_lists' => true]);
        $sub = $this->makeSubscription($plan);

        $this->assertTrue($sub->hasFeature('has_price_lists'));
    }

    public function test_active_subscription_without_feature_returns_false(): void
    {
        $plan = $this->makePlan(['has_price_lists' => false]);
        $sub = $this->makeSubscription($plan);

        $this->assertFalse($sub->hasFeature('has_price_lists'));
    }

    public function test_suspended_subscription_returns_false(): void
    {
        $plan = $this->makePlan(['has_price_lists' => true]);
        $sub = $this->makeSubscription($plan, ['status' => 'suspended']);

        $this->assertFalse($sub->hasFeature('has_price_lists'));
    }

    public function test_cancelled_subscription_returns_false(): void
    {
        $plan = $this->makePlan(['has_price_lists' => true]);
        $sub = $this->makeSubscription($plan, ['status' => 'cancelled']);

        $this->assertFalse($sub->hasFeature('has_price_lists'));
    }

    public function test_expired_trial_delegates_to_plan(): void
    {
        $plan = $this->makePlan(['has_advanced_stats' => true]);
        $sub = $this->makeSubscription($plan, [
            'status' => 'active',
            'trial_ends_at' => now()->subDays(1),
        ]);

        $this->assertTrue($sub->hasFeature('has_advanced_stats'));
    }
}
