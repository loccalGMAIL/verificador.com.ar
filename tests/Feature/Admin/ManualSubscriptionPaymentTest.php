<?php

namespace Tests\Feature\Admin;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ManualSubscriptionPaymentTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeSubscription(array $attributes = []): Subscription
    {
        $plan = Plan::create(['name' => 'Basic', 'price_usd' => 5, 'price_ars' => 5000]);
        $store = Store::create(['name' => 'Test Store', 'slug' => 'test-'.uniqid()]);

        return Subscription::create(array_merge([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(30),
        ], $attributes));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_registers_bank_transfer_payment(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $subscription = $this->makeSubscription(['next_payment_date' => '2026-11-10 00:00:00']);

        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.payments.store', $subscription), [
                'payment_method' => 'transfer',
                'amount' => 5000,
                'currency' => 'ARS',
                'paid_at' => '2026-11-05',
                'reference' => '000123456789',
                'notes' => 'Desde cuenta del titular',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $payment = SubscriptionPayment::first();
        $this->assertSame(SubscriptionPayment::METHOD_TRANSFER, $payment->payment_method);
        $this->assertSame('000123456789', $payment->reference);
        $this->assertSame('processed', $payment->status);
        $this->assertNull($payment->mp_payment_id);
        $this->assertSame('Transferencia', $payment->paymentMethodLabel());

        $this->assertSame('2026-12-10', $subscription->fresh()->next_payment_date->toDateString());
    }

    public function test_payment_on_overdue_subscription_extends_from_payment_date_and_activates_it(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $subscription = $this->makeSubscription([
            'status' => 'suspended',
            'next_payment_date' => '2026-09-01 00:00:00',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.payments.store', $subscription), [
                'payment_method' => 'cash',
                'amount' => 5000,
                'currency' => 'ARS',
                'paid_at' => '2026-11-05',
            ])
            ->assertSessionHasNoErrors();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertSame('2026-12-05', $subscription->next_payment_date->toDateString());
    }

    public function test_payment_without_due_date_extends_from_payment_date(): void
    {
        Carbon::setTestNow('2026-11-05 10:00:00');
        $subscription = $this->makeSubscription();

        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.payments.store', $subscription), [
                'payment_method' => 'other',
                'amount' => 5000,
                'currency' => 'ARS',
                'paid_at' => '2026-11-03',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('2026-12-03', $subscription->fresh()->next_payment_date->toDateString());
    }

    public function test_payment_method_is_validated(): void
    {
        $subscription = $this->makeSubscription();

        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.payments.store', $subscription), [
                'payment_method' => 'mercadopago',
                'amount' => 5000,
                'currency' => 'ARS',
                'paid_at' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('payment_method');

        $this->actingAs($this->admin())
            ->post(route('admin.subscriptions.payments.store', $subscription), [
                'payment_method' => 'transfer',
                'amount' => 5000,
                'currency' => 'ARS',
                'paid_at' => now()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('paid_at');

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_non_admin_cannot_register_payments(): void
    {
        $subscription = $this->makeSubscription();
        $owner = User::factory()->create(['role' => 'owner', 'store_id' => $subscription->store_id]);

        $this->actingAs($owner)
            ->post(route('admin.subscriptions.payments.store', $subscription), [
                'payment_method' => 'transfer',
                'amount' => 5000,
                'currency' => 'ARS',
                'paid_at' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_subscription_detail_shows_payment_method_and_reference(): void
    {
        $subscription = $this->makeSubscription();
        $subscription->payments()->create([
            'amount' => 5000,
            'currency' => 'ARS',
            'status' => 'processed',
            'payment_method' => 'transfer',
            'reference' => 'OP-777',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.subscriptions.show', $subscription))
            ->assertOk()
            ->assertSee('Transferencia')
            ->assertSee('Op. OP-777');
    }

    public function test_stores_index_links_to_subscription_statement(): void
    {
        $subscription = $this->makeSubscription();

        $this->actingAs($this->admin())
            ->get(route('admin.stores.index'))
            ->assertOk()
            ->assertSee(route('admin.subscriptions.show', $subscription), false)
            ->assertSee('Estado de cuenta');
    }
}
