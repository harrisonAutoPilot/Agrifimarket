<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_desk_and_settlement_flow(): void
    {
        $this->seed();

        $this->get('/')->assertOk()->assertSee('AgrifiMarket');
        $this->get('/prices')->assertOk()->assertSee('White maize');
        $this->get('/orders')->assertOk()->assertSee('Soybeans');
        $this->get('/marketplace')->assertOk()->assertSee('NPK');

        $buyer = User::query()->where('email', 'ibrahim.bello@agrifimarket.test')->firstOrFail();
        $supplier = User::query()->where('email', 'amina.yusuf@agrifimarket.test')->firstOrFail();
        $trade = Trade::query()->where('status', 'delivered')->firstOrFail();

        $this->actingAs($buyer)
            ->post(route('trades.transition', $trade), [
                'status' => 'quality_verified',
                'note' => 'Moisture within the order.',
            ])
            ->assertRedirect();

        $this->actingAs($buyer)->post(route('trades.pay', $trade))->assertRedirect();

        $trade->refresh();
        $supplier->wallet->refresh();

        $this->assertSame('payment_settled', $trade->status);
        $this->assertGreaterThan(1500000, (float) $supplier->wallet->balance);

        $this->actingAs($supplier)->get(route('matches.index'))->assertOk();
        $this->actingAs($buyer)->get(route('trades.show', $trade))->assertOk()->assertSee($trade->code);
    }
}
