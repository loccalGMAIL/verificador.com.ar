<?php

namespace Tests\Unit;

use App\Models\Plan;
use Tests\TestCase;

class PlanHasFeatureTest extends TestCase
{
    public function test_returns_true_when_feature_enabled_with_has_prefix(): void
    {
        $plan = new Plan;
        $plan->has_price_lists = true;

        $this->assertTrue($plan->hasFeature('has_price_lists'));
    }

    public function test_returns_true_when_feature_enabled_without_has_prefix(): void
    {
        $plan = new Plan;
        $plan->has_price_lists = true;

        $this->assertTrue($plan->hasFeature('price_lists'));
    }

    public function test_advanced_stats_feature_resolved_correctly(): void
    {
        $plan = new Plan;
        $plan->has_advanced_stats = true;

        // Validates the fix: ltrim('has_advanced_stats', 'has_') would have yielded 'dvanced_stats'
        $this->assertTrue($plan->hasFeature('has_advanced_stats'));
        $this->assertTrue($plan->hasFeature('advanced_stats'));
    }

    public function test_api_feature_resolved_correctly(): void
    {
        $plan = new Plan;
        $plan->has_api = true;

        // Validates the fix: ltrim('has_api', 'has_') would have yielded 'pi'
        $this->assertTrue($plan->hasFeature('has_api'));
        $this->assertTrue($plan->hasFeature('api'));
    }

    public function test_returns_false_when_feature_disabled(): void
    {
        $plan = new Plan;
        $plan->has_price_lists = false;

        $this->assertFalse($plan->hasFeature('has_price_lists'));
        $this->assertFalse($plan->hasFeature('price_lists'));
    }

    public function test_returns_false_for_nonexistent_feature(): void
    {
        $plan = new Plan;

        $this->assertFalse($plan->hasFeature('has_nonexistent'));
    }
}
