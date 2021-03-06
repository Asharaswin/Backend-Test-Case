<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['available'];

    public function borrowHistory()
    {
        return $this->hasMany(BookBorrow::class);
    }

    public function getAvailableAttribute()
    {
        $borrowed = $this->borrowHistory()->whereNUll('return_date')
                                        ->count();
        $avail = $this->quantity - $borrowed;
        return $avail;
    }
}
