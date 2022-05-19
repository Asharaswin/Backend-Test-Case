<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['is_penalized', 'book_borrowing'];

    public function bookBorrowed()
    {
        return $this->hasMany(BookBorrow::class);
    }

    public function getBookBorrowingAttribute()
    {
            return $this->bookBorrowed()->whereNull('return_date')->count();
    }

    public function allowBorrowing()
    {
        return $this->book_borrowing < 2;
    }

    public function getIsPenalizedAttribute()
    {
        $today = Carbon::now()->toDateString();

        return $this->penalized_end > $today;
    }

}
