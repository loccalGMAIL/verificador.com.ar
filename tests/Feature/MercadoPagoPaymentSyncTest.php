<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MercadoPagoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MercadoPagoPaymentSyncTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mercadopago.access_token' => 'test-token']);
    }

    private function makeSubscription(string $mpSubscriptionId, string $status = 'active'): Subscription
    {
        $plan = Plan::create(['name' => 'Basic', 'price_usd' => 5, 'price_ars' => 5000]);
        $store = Store::create(['name' => 'Test Store', 'slug' => 'test-'.uniqid()]);

        return Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'starts_at' => now()->subDays(30),
            'mp_subscription_id' => $mpSubscriptionId,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $payments
     */
    private function mockMercadoPago(array $payments, array $preapproval = ['status' => 'authorized', 'payer_id' => null]): void
    {
        $this->mock(MercadoPagoService::class, function ($mock) use ($payments, $preapproval) {
            $mock->shouldReceive('getPreapproval')->andReturn($preapproval);
            $mock->shouldReceive('searchAuthorizedPayments')->andReturn($payments);
        });
    }

    public function test_command_syncs_processed_and_rejected_payments(): void
    {
        $this->makeSubscription('sub_123');

        $this->mockMercadoPago([
            [
                'id' => 'pay_ok',
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 5000.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'date_approved' => '2026-06-01T12:00:00.000-03:00',
                'debit_date' => '2026-06-01T00:00:00.000-03:00',
            ],
            [
                'id' => 'pay_rejected',
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 5000.0,
                'currency_id' => 'ARS',
                'status' => 'recycling',
                'status_detail' => 'cc_rejected_insufficient_amount',
                'debit_date' => '2026-07-01T00:00:00.000-03:00',
            ],
        ]);

        $this->artisan('mp:sync-payments')->assertSuccessful();

        $this->assertDatabaseHas('subscription_payments', [
            'mp_payment_id' => 'pay_ok',
            'status' => 'processed',
            'status_detail' => 'accredited',
        ]);

        $this->assertDatabaseHas('subscription_payments', [
            'mp_payment_id' => 'pay_rejected',
            'status' => 'recycling',
            'status_detail' => 'cc_rejected_insufficient_amount',
        ]);
    }

    public function test_command_is_idempotent(): void
    {
        $this->makeSubscription('sub_123');

        $this->mockMercadoPago([
            [
                'id' => 'pay_ok',
                'transaction_amount' => 5000.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'date_approved' => '2026-06-01T12:00:00.000-03:00',
                'debit_date' => '2026-06-01T00:00:00.000-03:00',
            ],
        ]);

        $this->artisan('mp:sync-payments')->assertSuccessful();
        $this->artisan('mp:sync-payments')->assertSuccessful();

        $this->assertDatabaseCount('subscription_payments', 1);
    }

    public function test_command_updates_next_payment_date_only_from_processed_payments(): void
    {
        $subscription = $this->makeSubscription('sub_123');
        $debitDate = '2026-06-01T00:00:00.000-03:00';

        $this->mockMercadoPago([
            [
                'id' => 'pay_ok',
                'transaction_amount' => 5000.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'date_approved' => '2026-06-01T12:00:00.000-03:00',
                'debit_date' => $debitDate,
            ],
            [
                'id' => 'pay_rejected',
                'transaction_amount' => 5000.0,
                'currency_id' => 'ARS',
                'status' => 'recycling',
                'status_detail' => 'cc_rejected_insufficient_amount',
                'debit_date' => '2026-05-01T00:00:00.000-03:00',
            ],
        ]);

        $this->artisan('mp:sync-payments')->assertSuccessful();

        $subscription->refresh();
        $this->assertNotNull($subscription->next_payment_date);
        $this->assertTrue(Carbon::parse($debitDate)->addMonth()->isSameDay($subscription->next_payment_date));
    }

    public function test_command_activates_pending_subscription_from_preapproval_status(): void
    {
        $subscription = $this->makeSubscription('sub_123', 'trial');

        $this->mockMercadoPago([], ['status' => 'authorized', 'payer_id' => 'payer_9', 'payer_email' => 'payer@test.com']);

        $this->artisan('mp:sync-payments')->assertSuccessful();

        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
        $this->assertEquals('payer_9', $subscription->mp_payer_id);
        $this->assertEquals('payer@test.com', $subscription->mp_payer_email);
    }

    public function test_command_skips_subscriptions_without_mp_id(): void
    {
        $plan = Plan::create(['name' => 'Free', 'price_usd' => 0, 'price_ars' => 0]);
        $store = Store::create(['name' => 'Free Store', 'slug' => 'free-'.uniqid()]);
        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldNotReceive('getPreapproval');
            $mock->shouldNotReceive('searchAuthorizedPayments');
        });

        $this->artisan('mp:sync-payments')->assertSuccessful();

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_admin_can_sync_subscription_from_mp(): void
    {
        $subscription = $this->makeSubscription('sub_123');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->mockMercadoPago([
            [
                'id' => 'pay_ok',
                'transaction_amount' => 5000.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'date_approved' => '2026-06-01T12:00:00.000-03:00',
                'debit_date' => '2026-06-01T00:00:00.000-03:00',
            ],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.sync-mp', $subscription))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subscription_payments', [
            'subscription_id' => $subscription->id,
            'mp_payment_id' => 'pay_ok',
            'status' => 'processed',
        ]);
    }

    public function test_non_admin_cannot_sync_subscription_from_mp(): void
    {
        $subscription = $this->makeSubscription('sub_123');
        $owner = User::factory()->create(['role' => 'owner', 'store_id' => $subscription->store_id]);

        $this->actingAs($owner)
            ->post(route('admin.subscriptions.sync-mp', $subscription))
            ->assertForbidden();

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_admin_sync_fails_gracefully_without_mp_subscription_id(): void
    {
        $plan = Plan::create(['name' => 'Free', 'price_usd' => 0, 'price_ars' => 0]);
        $store = Store::create(['name' => 'Free Store', 'slug' => 'free-'.uniqid()]);
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.sync-mp', $subscription))
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
