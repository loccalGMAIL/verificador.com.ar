<?php

namespace Tests\Feature\Webhook;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\MercadoPagoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MercadoPagoWebhookTest extends TestCase
{
    use LazilyRefreshDatabase;

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

    private function postWebhook(string $type, string $mpId): TestResponse
    {
        return $this->postJson('/webhooks/mercadopago', [
            'type' => $type,
            'data' => ['id' => $mpId],
        ]);
    }

    public function test_authorized_payment_creates_payment_with_debit_date_and_status_detail(): void
    {
        $subscription = $this->makeSubscription('sub_123');
        $debitDate = '2026-05-01T00:00:00.000-03:00';

        $this->mock(MercadoPagoService::class, function ($mock) use ($debitDate) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getAuthorizedPayment')->with('pay_456')->andReturn([
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 1500.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'date_approved' => '2026-05-01T12:00:00.000-03:00',
                'debit_date' => $debitDate,
            ]);
        });

        $this->postWebhook('subscription_authorized_payment', 'pay_456')->assertOk();

        $this->assertDatabaseHas('subscription_payments', [
            'mp_payment_id' => 'pay_456',
            'status' => 'processed',
            'status_detail' => 'accredited',
            'currency' => 'ARS',
        ]);

        $payment = SubscriptionPayment::where('mp_payment_id', 'pay_456')->first();
        $this->assertNotNull($payment->debit_date);
        $this->assertTrue(Carbon::parse($debitDate)->isSameDay($payment->debit_date));
    }

    public function test_authorized_payment_updates_next_payment_date_on_subscription(): void
    {
        $subscription = $this->makeSubscription('sub_123');
        $debitDate = '2026-05-01T00:00:00.000-03:00';
        $expectedNext = Carbon::parse($debitDate)->addMonth();

        $this->mock(MercadoPagoService::class, function ($mock) use ($debitDate) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getAuthorizedPayment')->andReturn([
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 1500.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'date_approved' => '2026-05-01T12:00:00.000-03:00',
                'debit_date' => $debitDate,
            ]);
        });

        $this->postWebhook('subscription_authorized_payment', 'pay_456')->assertOk();

        $subscription->refresh();
        $this->assertNotNull($subscription->next_payment_date);
        $this->assertTrue($expectedNext->isSameDay($subscription->next_payment_date));
    }

    public function test_recycling_payment_does_not_update_next_payment_date(): void
    {
        $subscription = $this->makeSubscription('sub_123');

        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getAuthorizedPayment')->andReturn([
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 1500.0,
                'currency_id' => 'ARS',
                'status' => 'recycling',
                'status_detail' => 'cc_rejected_insufficient_amount',
                'debit_date' => '2026-05-01T00:00:00.000-03:00',
            ]);
        });

        $this->postWebhook('subscription_authorized_payment', 'pay_789')->assertOk();

        $this->assertDatabaseHas('subscription_payments', [
            'mp_payment_id' => 'pay_789',
            'status' => 'recycling',
            'status_detail' => 'cc_rejected_insufficient_amount',
        ]);

        $subscription->refresh();
        $this->assertNull($subscription->next_payment_date);
    }

    public function test_recycling_payment_logs_warning(): void
    {
        $this->makeSubscription('sub_123');
        Log::spy();

        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getAuthorizedPayment')->andReturn([
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 1500.0,
                'currency_id' => 'ARS',
                'status' => 'recycling',
                'status_detail' => 'cc_rejected_insufficient_amount',
                'debit_date' => '2026-05-01T00:00:00.000-03:00',
            ]);
        });

        $this->postWebhook('subscription_authorized_payment', 'pay_789')->assertOk();

        Log::shouldHaveReceived('warning')->once()->withArgs(function ($message) {
            return str_contains($message, 'recycling');
        });
    }

    public function test_preapproval_sets_initial_next_payment_date_when_transitioning_to_active(): void
    {
        $subscription = $this->makeSubscription('sub_123', 'trial');
        $this->assertNull($subscription->next_payment_date);

        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getPreapproval')->with('sub_123')->andReturn([
                'status' => 'authorized',
                'payer_id' => null,
            ]);
        });

        $this->postWebhook('subscription_preapproval', 'sub_123')->assertOk();

        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
        $this->assertNotNull($subscription->next_payment_date);
        $this->assertTrue($subscription->next_payment_date->isFuture());
    }

    public function test_preapproval_does_not_overwrite_existing_next_payment_date(): void
    {
        $subscription = $this->makeSubscription('sub_123', 'active');
        $existingDate = now()->addDays(15);
        $subscription->update(['next_payment_date' => $existingDate]);

        $this->mock(MercadoPagoService::class, function ($mock) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getPreapproval')->with('sub_123')->andReturn([
                'status' => 'authorized',
                'payer_id' => null,
            ]);
        });

        // Status ya es 'active', el webhook no debería cambiar nada
        $this->postWebhook('subscription_preapproval', 'sub_123')->assertOk();

        $subscription->refresh();
        $this->assertTrue($existingDate->isSameDay($subscription->next_payment_date));
    }

    public function test_authorized_payment_is_idempotent(): void
    {
        $this->makeSubscription('sub_123');
        $debitDate = '2026-05-01T00:00:00.000-03:00';

        $this->mock(MercadoPagoService::class, function ($mock) use ($debitDate) {
            $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
            $mock->shouldReceive('getAuthorizedPayment')->andReturn([
                'preapproval_id' => 'sub_123',
                'transaction_amount' => 1500.0,
                'currency_id' => 'ARS',
                'status' => 'processed',
                'status_detail' => 'accredited',
                'date_approved' => '2026-05-01T12:00:00.000-03:00',
                'debit_date' => $debitDate,
            ]);
        });

        $this->postWebhook('subscription_authorized_payment', 'pay_456')->assertOk();
        $this->postWebhook('subscription_authorized_payment', 'pay_456')->assertOk();

        $this->assertDatabaseCount('subscription_payments', 1);
    }
}
