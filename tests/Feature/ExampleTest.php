<?php

namespace Tests\Feature;

use App\Models\CafeSession;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\PosTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_can_login_with_email_and_create_tables_and_menu_items(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);

        $this->post(route('login.store'), [
            'login' => 'admin@example.com',
            'password' => 'admin123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);

        $this->post(route('tables.store'), [
            'name' => 'طاولة اختبار',
            'section' => 'vip',
            'seats' => 6,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas(PosTable::class, [
            'name' => 'طاولة اختبار',
            'section' => 'vip',
            'status' => 'free',
        ]);

        $this->post(route('menu-items.store'), [
            'name' => 'قهوة اختبار',
            'category' => 'drink',
            'price' => 7.5,
            'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas(MenuItem::class, [
            'name' => 'قهوة اختبار',
            'category' => 'drink',
        ]);
    }

    public function test_admin_can_update_and_delete_tables(): void
    {
        $admin = User::factory()->admin()->create();
        $table = PosTable::create(['name' => 'طاولة قديمة', 'section' => 'indoor', 'status' => 'free', 'seats' => 2]);

        $this->actingAs($admin)
            ->patch(route('tables.update', $table), [
                'name' => 'طاولة جديدة',
                'section' => 'outdoor',
                'status' => 'reserved',
                'seats' => 4,
                'sort_order' => 9,
            ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas(PosTable::class, [
            'id' => $table->id,
            'name' => 'طاولة جديدة',
            'section' => 'outdoor',
            'status' => 'reserved',
            'seats' => 4,
            'sort_order' => 9,
        ]);

        $this->actingAs($admin)
            ->delete(route('tables.destroy', $table))
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSoftDeleted('pos_tables', ['id' => $table->id]);
    }

    public function test_user_can_change_password_and_logout_keeps_admin_account(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'old-password',
        ]);

        $this->actingAs($admin)
            ->patch(route('profile.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $admin->fresh()->password));

        $this->actingAs($admin)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertDatabaseHas(User::class, ['id' => $admin->id, 'username' => 'admin']);
        $this->assertGuest();

        $this->post(route('login.store'), [
            'login' => 'admin@example.com',
            'password' => 'new-password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_cashier_can_open_table_add_order_and_close_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $table = PosTable::create(['name' => 'طاولة 1', 'section' => 'indoor', 'status' => 'free']);
        $item = MenuItem::create(['name' => 'لاتيه', 'category' => 'drink', 'price' => 10, 'is_active' => true]);

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
            'item_name' => 'لاتيه',
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
