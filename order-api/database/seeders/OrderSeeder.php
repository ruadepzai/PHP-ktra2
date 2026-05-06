<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đảm bảo có ít nhất 2 user để test phân quyền
        $users = User::all();
        
        if ($users->isEmpty()) {
            $users = collect([
                User::create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                ]),
                User::create([
                    'name' => 'Other User',
                    'email' => 'other@example.com',
                    'password' => bcrypt('password'),
                ])
            ]);
        }

        $items = ['iPhone 15 Pro', 'MacBook Air M3', 'AirPods Pro', 'iPad Pro', 'Apple Watch'];
        $methods = ['COD', 'Bank Transfer', 'Momo', 'ZaloPay', 'VNPay'];

        foreach ($users as $user) {
            // Mỗi user tạo 5 đơn hàng với các trạng thái khác nhau
            $statuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
            
            foreach ($statuses as $i => $status) {
                Order::create([
                    'user_id'          => $user->id,
                    'order_number'     => 'ORD-' . strtoupper(Str::random(8)),
                    'item_name'        => $items[$i],
                    'quantity'         => rand(1, 5),
                    'total_price'      => rand(100, 5000) * 1000,
                    'status'           => $status,
                    'shipping_address' => 'Số ' . rand(1, 100) . ' Đường ABC, Quận XYZ, TP.HCM',
                    'payment_method'   => $methods[$i],
                    'note'             => 'Ghi chú cho đơn hàng ' . $status,
                ]);
            }
        }
    }
}
