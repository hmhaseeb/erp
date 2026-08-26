<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }

    public function test_all_erp_routes_render_successfully(): void
    {
        $user = User::factory()->create();

        $routes = [
            'dashboard',
            'accounts.index',
            'accounts.transactions',
            'accounts.ledger',
            'products.index',
            'products.categories',
            'products.units',
            'products.stock',
            'suppliers.index',
            'customers.index',
            'purchases.index',
            'purchases.create',
            'purchases.returns',
            'sales.index',
            'sales.create',
            'sales.returns',
            'payments.customer',
            'payments.supplier',
            'income.categories',
            'income.index',
            'expenses.categories',
            'expenses.index',
            'reports.daily',
            'reports.sales',
            'reports.purchases',
            'reports.stock',
            'reports.cashbook',
            'reports.bankbook',
            'reports.receivables',
            'reports.payables',
            'reports.profit-loss',
            'settings.company',
            'settings.invoice',
            'settings.logos',
            'settings.general',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get(route($route));
            $response->assertStatus(200);
        }
    }
}
