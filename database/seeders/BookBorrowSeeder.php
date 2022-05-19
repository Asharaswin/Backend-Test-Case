<?php

namespace Database\Seeders;

use App\Models\BookBorrow;
use Illuminate\Database\Seeder;

class BookBorrowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BookBorrow::factory()->count(30)->create();
    }
}
