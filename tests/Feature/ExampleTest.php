<?php

namespace Tests\Feature;

use App\Models\CafeSession;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\PosTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_cashier_can_open_table_add_order_and_close_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $table = PosTable::create(['name' => 'T1', 'section' => 'indoor', 'status' => 'free']);
        $item = MenuItem::create(['name' => 'Latte', 'category' => 'drink', 'price' => 10, 'is_active' => true]);

        $this->actingAs($cashier)
            ->post(route('tables.open', $table))
            ->assertRedirect();

        $session = CafeSession::firstOrFail();
        $this->assertSame('busy', $table->fresh()->status);
        $this->assertStringStartsWith('INV-', $session->invoice_number);

        $this->actingAs($cashier)
            ->post(route('orders.store', $session), ['menu_item_id' => $item->id, 'quantity' => 2])
            ->assertRedirect();

        $this->assertDatabaseHas(OrderItem::class, [
            'cafe_session_id' => $session->id,
            'item_name' => 'Latte',
            'quantity' => 2,
        ]);

        $this->actingAs($cashier)
            ->post(route('sessions.close', $session), ['discount' => 1, 'tip' => 2])
            ->assertRedirect(route('sessions.invoice', $session));

        $session = $session->fresh();
        $this->assertSame('closed', $session->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertEquals(21.00, (float) $session->total_price);
    }

    public function test_admin_only_pages_are_protected(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($admin)->get(route('reports.daily'))->assertOk();
        $this->actingAs($cashier)->get(route('menu-items.index'))->assertForbidden();
    }
}
