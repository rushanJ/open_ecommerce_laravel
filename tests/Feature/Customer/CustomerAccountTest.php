<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $email = 'new-customer-'.uniqid().'@example.test';

        $this->post(route('customer.register.store'), [
            'first_name' => 'New',
            'last_name' => 'Customer',
            'email' => $email,
            'phone' => '077'.random_int(1000000, 9999999),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accepts_marketing' => false,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.account.dashboard'));

        $this->assertDatabaseHas('customers', [
            'email' => $email,
        ]);

        $this->assertAuthenticated('customer');
    }

    public function test_customer_can_login(): void
    {
        /** @var Customer $customer */
        $customer = Customer::factory()->create([
            'email' => 'login@example.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->post(route('customer.login.submit'), [
            'email' => $customer->email,
            'password' => 'password123',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.account.dashboard'));

        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_can_logout(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'customer')
            ->post(route('customer.logout'))
            ->assertRedirect(route('customer.home'));

        $this->assertGuest('customer');
    }

    public function test_customer_can_update_profile(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'Old',
        ]);

        $this->actingAs($customer, 'customer')
            ->put(route('customer.account.profile.update'), [
                'first_name' => 'New',
                'last_name' => 'Name',
                'phone' => '077'.random_int(1000000, 9999999),
                'accepts_marketing' => true,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.account.profile.edit'));

        $customer->refresh();
        $this->assertSame('New', $customer->first_name);
    }

    public function test_customer_can_add_address(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer, 'customer')
            ->post(route('customer.account.addresses.store'), [
                'type' => 'shipping',
                'first_name' => 'Addr',
                'last_name' => 'One',
                'phone' => '0773333333',
                'email' => 'addr@example.test',
                'address_line_1' => 'Line 1',
                'city' => 'Colombo',
                'district' => 'Colombo',
                'province' => 'Western',
                'country_code' => 'LK',
                'is_default' => true,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customer.account.addresses.index'));

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'address_line_1' => 'Line 1',
        ]);
    }

    public function test_customer_cannot_access_another_customers_address(): void
    {
        $a = $this->createCustomer();
        $b = $this->createCustomer();

        /** @var CustomerAddress $address */
        $address = CustomerAddress::query()->create([
            'customer_id' => $b->id,
            'type' => 'shipping',
            'first_name' => 'Other',
            'last_name' => null,
            'phone' => null,
            'email' => null,
            'address_line_1' => 'Secret',
            'address_line_2' => null,
            'city' => 'Colombo',
            'district' => null,
            'province' => null,
            'postal_code' => null,
            'country_code' => 'LK',
            'is_default' => false,
        ]);

        $this->actingAs($a, 'customer')
            ->get(route('customer.account.addresses.edit', $address))
            ->assertNotFound();
    }

    public function test_customer_can_view_own_order(): void
    {
        $customer = $this->createCustomer();

        /** @var Order $order */
        $order = Order::factory()->forCustomer($customer)->create([
            'order_number' => 'MEK-TEST-OWN-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '100.0000',
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.account.orders.show', $order))
            ->assertOk();
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $a = $this->createCustomer();
        $b = $this->createCustomer();

        /** @var Order $order */
        $order = Order::factory()->forCustomer($b)->create([
            'order_number' => 'MEK-TEST-OTHER-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '100.0000',
        ]);

        $this->actingAs($a, 'customer')
            ->get(route('customer.account.orders.show', $order))
            ->assertNotFound();
    }

    public function test_guest_order_lookup_works_with_correct_email_or_phone(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'customer_id' => null,
            'customer_email' => 'guest@example.test',
            'customer_phone' => '0774444444',
            'order_number' => 'MEK-LOOKUP-OK-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '100.0000',
        ]);

        $this->post(route('customer.order.lookup.submit'), [
            'order_number' => $order->order_number,
            'contact' => 'guest@example.test',
        ])
            ->assertOk()
            ->assertViewHas('notFound', false);

        $this->post(route('customer.order.lookup.submit'), [
            'order_number' => $order->order_number,
            'contact' => '0774444444',
        ])
            ->assertOk()
            ->assertViewHas('notFound', false);
    }

    public function test_guest_order_lookup_fails_with_wrong_contact(): void
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'customer_id' => null,
            'customer_email' => 'guest@example.test',
            'customer_phone' => '0774444444',
            'order_number' => 'MEK-LOOKUP-BAD-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '100.0000',
        ]);

        $this->post(route('customer.order.lookup.submit'), [
            'order_number' => $order->order_number,
            'contact' => 'wrong@example.test',
        ])
            ->assertOk()
            ->assertViewHas('notFound', true);
    }
}
