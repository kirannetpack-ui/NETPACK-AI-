<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => User::where('user_type', 'seller')->inRandomOrder()->first()->id ?? 1,
            'customer_id' => User::where('user_type', 'client')->inRandomOrder()->first()->id ?? 1,
            'customer_name' => $this->faker->name,
            'customer_email' => $this->faker->email,
            'customer_phone' => $this->faker->phoneNumber,
            'customer_address' => $this->faker->address,
            'product_id' => Product::inRandomOrder()->first()->id ?? 1,
            'product_name' => $this->faker->words(3, true),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->numberBetween(100, 1000),
            'total_amount' => function($attributes) {
                return $attributes['quantity'] * $attributes['unit_price'];
            },
            'shipping_cost' => $this->faker->numberBetween(50, 200),
            'tax_amount' => function($attributes) {
                return $attributes['total_amount'] * 0.13;
            },
            'discount_amount' => 0,
            'grand_total' => function($attributes) {
                return $attributes['total_amount'] + $attributes['shipping_cost'] + $attributes['tax_amount'] - $attributes['discount_amount'];
            },
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'completed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'bank_transfer', 'e-wallet', 'cod']),
            'shipping_method' => $this->faker->randomElement(['standard', 'express', 'overnight']),
            'tracking_number' => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{10}'),
            'shipped_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'delivered_at' => $this->faker->optional()->dateTimeBetween('-20 days', 'now'),
            'notes' => $this->faker->optional()->sentence,
        ];
    }
}