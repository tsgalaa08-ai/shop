<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create(['name' => 'Админ', 'email' => 'admin@example.com', 'password' => 'ChangeMe123!', 'role' => 'admin']);
        foreach (['Саван', 'Шампунь', 'Арьс арчилгаа', 'Бэлгийн багц', 'Гэр ахуй'] as $name) { $category = Category::create(['name' => $name, 'slug' => str($name)->slug()]); for ($i = 1; $i <= 2; $i++) Product::create(['category_id' => $category->id, 'name' => $name.' '.$i, 'slug' => str($name.' '.$i)->slug(), 'sku' => strtoupper(str($name)->substr(0, 3)).'-'.$i, 'description' => 'Өдөр тутмын хэрэглээнд тохиромжтой чанартай бүтээгдэхүүн.', 'price' => 15000 + $i * 2500, 'stock' => 10]); }
        DB::table('settings')->insert(['key' => 'delivery_fee', 'value' => '5000', 'created_at' => now(), 'updated_at' => now()]);
    }
}
