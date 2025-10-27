<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $income = ['Gaji','Bonus','Investasi','Hadiah','Penjualan'];
        $expense = ['Makan','Transport','Sewa','Listrik','Internet','Hiburan','Kesehatan','Pendidikan','Belanja'];

        foreach ($income as $name) {
            Category::firstOrCreate(['name' => $name, 'kind' => 'income']);
        }
        foreach ($expense as $name) {
            Category::firstOrCreate(['name' => $name, 'kind' => 'expense']);
        }
    }
}
