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

    public function test_dashboard_shows_admin_table_controls_only_to_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->create(['role' => 'cashier']);
        PosTable::create(['name' => 'طاولة الواجهة', 'section' => 'indoor', 'status' => 'free']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('أدوات تعديل وحذف الطاولات مفعلة')
            ->assertSee('حذف الطاولة');

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('تعديل وحذف الطاولات يظهر فقط لحساب الأدمن')
            ->assertDontSee('حذف الطاولة');
    }

    public function test_seed_adds_requested_drink_menu_items(): void
    {
        $this->seed();

        $this->assertDatabaseHas(MenuItem::class, [
            'name' => 'كوكتيل - أفوكادو سنشل مع قشطة',
            'category' => 'drink',
        ]);

        $this->assertDatabaseHas(MenuItem::class, [
            'name' => 'سموذي - بطيخ',
            'category' => 'drink',
        ]);

        $this->assertDatabaseHas(MenuItem::class, [
            'name' => 'موهيتو طاقة - لوجو بلو',
            'category' => 'drink',
        ]);

        $this->assertDatabaseHas(MenuItem::class, [
            'name' => 'شيك - ليمون نعناع',
            'category' => 'drink',
        ]);
    }

    public function test_order_screen_groups_menu_items_by_type(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $table = PosTable::create(['name' => 'طاولة مجموعات', 'section' => 'indoor', 'status' => 'busy']);
        $session = CafeSession::create([
            'user_id' => $cashier->id,
            'pos_table_id' => $table->id,
            'invoice_number' => 'INV-TEST-GROUPS',
            'status' => 'open',
            'opened_at' => now(),
        ]);

        MenuItem::create(['name' => 'كوكتيل - منجا', 'category' => 'drink', 'price' => 2, 'is_active' => true]);
        MenuItem::create(['name' => 'سموذي - بطيخ', 'category' => 'drink', 'price' => 2.25, 'is_active' => true]);
        MenuItem::create(['name' => 'قهوة باردة - ايس لاتيه', 'category' => 'drink', 'price' => 1.75, 'is_active' => true]);
        MenuItem::create(['name' => 'موهيتو طاقة - فراولة', 'category' => 'drink', 'price' => 1.25, 'is_active' => true]);
        MenuItem::create(['name' => 'ملك شيك - أوريو', 'category' => 'drink', 'price' => 2, 'is_active' => true]);
        MenuItem::create(['name' => 'شيك - موز', 'category' => 'drink', 'price' => 1, 'is_active' => true]);
        MenuItem::create(['name' => 'شيشة نعناع', 'category' => 'shisha', 'price' => 10, 'is_active' => true]);

        $this->actingAs($cashier)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('الأصناف مقسمة حسب النوع')
            ->assertSeeInOrder(['كوكتيل', 'كوكتيل - منجا'])
            ->assertSeeInOrder(['سموذي', 'سموذي - بطيخ'])
            ->assertSeeInOrder(['قهوة باردة وقهوة', 'قهوة باردة - ايس لاتيه'])
            ->assertSeeInOrder(['موهيتو طاقة', 'موهيتو طاقة - فراولة'])
            ->assertSeeInOrder(['ملك شيك', 'ملك شيك - أوريو'])
            ->assertSeeInOrder(['شيك', 'شيك - موز'])
            ->assertSeeInOrder(['شيشة', 'شيشة نعناع']);
    }

    public function test_daily_report_and_invoice_handle_soft_deleted_tables(): void
    {
        $admin = User::factory()->admin()->create();
        $table = PosTable::create(['name' => 'طاولة محذوفة سابقاً', 'section' => 'indoor', 'status' => 'free']);
        $session = CafeSession::create([
            'user_id' => $admin->id,
            'pos_table_id' => $table->id,
            'invoice_number' => 'INV-DELETED-TABLE',
            'status' => 'closed',
            'opened_at' => now()->subHour(),
            'closed_at' => now(),
            'subtotal' => 5,
            'total_price' => 5,
        ]);
        $session->orderItems()->create([
            'item_name' => 'قهوة اختبار',
            'price' => 5,
            'quantity' => 1,
        ]);

        $table->delete();

        $this->actingAs($admin)
            ->get(route('reports.daily'))
            ->assertOk()
            ->assertSee('INV-DELETED-TABLE')
            ->assertSee('طاولة محذوفة سابقاً');

        $this->actingAs($admin)
            ->get(route('sessions.invoice', $session))
            ->assertOk()
            ->assertSee('طاولة محذوفة سابقاً');
    }

    public function test_daily_report_shows_type_totals_and_separate_shisha_breakdown(): void
    {
        $admin = User::factory()->admin()->create();
        $table = PosTable::create(['name' => 'طاولة التقرير', 'section' => 'indoor', 'status' => 'free']);
        $session = CafeSession::create([
            'user_id' => $admin->id,
            'pos_table_id' => $table->id,
            'invoice_number' => 'INV-REPORT-GROUPS',
            'status' => 'closed',
            'opened_at' => now()->subHour(),
            'closed_at' => now(),
            'subtotal' => 20,
            'total_price' => 20,
        ]);

        $shisha = MenuItem::create(['name' => 'شيشة نعناع', 'category' => 'shisha', 'price' => 10, 'is_active' => true]);
        $cocktail = MenuItem::create(['name' => 'كوكتيل - منجا', 'category' => 'drink', 'price' => 2, 'is_active' => true]);
        $coffee = MenuItem::create(['name' => 'قهوة باردة - ايس لاتيه', 'category' => 'drink', 'price' => 1.75, 'is_active' => true]);

        $session->orderItems()->create(['menu_item_id' => $shisha->id, 'item_name' => $shisha->name, 'price' => 10, 'quantity' => 2]);
        $session->orderItems()->create(['menu_item_id' => $cocktail->id, 'item_name' => $cocktail->name, 'price' => 2, 'quantity' => 3]);
        $session->orderItems()->create(['menu_item_id' => $coffee->id, 'item_name' => $coffee->name, 'price' => 1.75, 'quantity' => 1]);

        $this->actingAs($admin)
            ->get(route('reports.daily'))
            ->assertOk()
            ->assertSee('إجمالي الكميات حسب النوع')
            ->assertSeeInOrder(['كوكتيل', '3'])
            ->assertSeeInOrder(['قهوة', '1'])
            ->assertSeeInOrder(['شيشة', '2'])
            ->assertSee('الشيشة - تفصيل مستقل')
            ->assertSee('إجمالي الشيشة: 2')
            ->assertSeeInOrder(['شيشة نعناع', '2']);
    }

    public function test_admin_can_update_and_delete_menu_items(): void
    {
        $admin = User::factory()->admin()->create();
        $item = MenuItem::create(['name' => 'صنف قديم', 'category' => 'drink', 'price' => 1, 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('menu-items.update', $item), [
                'name' => 'صنف معدل',
                'category' => 'food',
                'price' => 2.5,
                'is_active' => 1,
            ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas(MenuItem::class, [
            'id' => $item->id,
            'name' => 'صنف معدل',
            'category' => 'food',
        ]);

        $this->actingAs($admin)
            ->delete(route('menu-items.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing(MenuItem::class, ['id' => $item->id]);
    }

    public function test_open_session_can_be_cancelled_without_counting_sales(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $admin = User::factory()->admin()->create();
        $table = PosTable::create(['name' => 'طاولة كنسل', 'section' => 'indoor', 'status' => 'free']);
        $item = MenuItem::create(['name' => 'كوكتيل - تجربة', 'category' => 'drink', 'price' => 3, 'is_active' => true]);

        $this->actingAs($cashier)
            ->post(route('tables.open', $table))
            ->assertRedirect();

        $session = CafeSession::firstOrFail();

        $this->actingAs($cashier)
            ->post(route('orders.store', $session), ['menu_item_id' => $item->id, 'quantity' => 2])
            ->assertRedirect();

        $this->actingAs($cashier)
            ->post(route('sessions.cancel', $session))
            ->assertRedirect(route('dashboard'));

        $session = $session->fresh();
        $this->assertSame('cancelled', $session->status);
        $this->assertSame('free', $table->fresh()->status);
        $this->assertEquals(0.00, (float) $session->total_price);

        $this->actingAs($admin)
            ->get(route('reports.daily'))
            ->assertOk()
            ->assertDontSee($session->invoice_number)
            ->assertSee('<span>عدد الفواتير</span><strong>0</strong>', false);
    }

    public function test_session_and_dashboard_show_elapsed_session_timer(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $table = PosTable::create(['name' => 'طاولة مؤقت', 'section' => 'indoor', 'status' => 'busy']);
        $session = CafeSession::create([
            'user_id' => $cashier->id,
            'pos_table_id' => $table->id,
            'invoice_number' => 'INV-TIMER',
            'status' => 'open',
            'opened_at' => now()->subMinutes(12),
        ]);

        $this->actingAs($cashier)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('مدة الجلسة')
            ->assertSee('session-timer');

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-table-timer="'.$table->id.'"', false);

        $this->actingAs($cashier)
            ->getJson(route('tables.state'))
            ->assertOk()
            ->assertJsonPath('tables.0.active_session_opened_at', $session->opened_at->toIso8601String());
    }
}
