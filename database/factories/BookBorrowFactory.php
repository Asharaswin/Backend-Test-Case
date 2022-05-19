<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookBorrowFactory extends Factory
{
    public function definition()
    {
        $book = Book::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();
        $odd_even = rand(0,1);
        $date = $this->faker->dateTimeBetween($startDate = '-20 days', $endDate = '-1 days', $timezone = null);
        $due_date = Carbon::parse($date)->addDays(7)->toDateString();
        // dd($due_date);
        if ($odd_even) {
            $return_date = Carbon::now()->subDays()->toDateString();
        } else {
            $return_date = null;
        }


        if ($book->available > 0 && !($user->is_borrowing)) {
            

            return [
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'book_id'   => $book->id,
                'book_code' => $book->code,
                'book_title' => $book->title,
                'borrow_date'   => $date,
                'due_date'   => $due_date,
                'return_date'   => $return_date,
            ];
        }
    }
}
