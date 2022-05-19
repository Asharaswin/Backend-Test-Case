<?php

namespace App\Http\Controllers\api\v1;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\User;
use App\Models\BookBorrow;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function list(Request $request)
    {
        $book = Book::query();
        if ($request->search != null) {
            $book = $book->where('title', $request->search)
                            ->orWhere('code', $request->search);
        }
        
        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['error']       = '';
        $success['data']        = $book->get();
        return response()->json($success, 200); 
    }

    public function borrow(Request $request)
    {
        $validator = $this->validator($request);
        if ($validator->fails()) return response()->json(['message' => __('main.msg_borrow_book'), 'errors' => $validator->errors()], 400);
        
        $book = Book::where('code', $request->book_code)->first();
        $user = User::find($request->user_id);
        
        $date       = Carbon::now()->toDateString();
        $due_date   = Carbon::parse($date)->addDays(7)->toDateString();
        
        if($user == null or $book == null) return response()->json(['message' => __('main.msg_not_found'),], 404);
        if ($user->is_penalized) return response()->json(['message' => __('main.msg_penalized'). date('d M Y', strtotime($user->penalized_end)), ], 201);
        if (!$user->allowBorrowing()) return response()->json([ 'message' => __('main.msg_limit_borrow'),], 201);
        if ($book->available == 0) return response()->json([ 'message' => __('main.msg_not_avail'), ], 201);
        
        $borrow = BookBorrow::create([
            'user_id'       => $user->id,
            'user_name'     => $user->name,
            'book_id'       => $book->id,
            'book_code'     => $book->code,
            'book_title'    => $book->title,
            'due_date'      => $due_date,
            'borrow_date'   => $date,
        ]);

        $success['success']     = true;
        $success['message']     = 'buku berhasil dipinjam';
        $success['error']       = '';
        $success['data']        = $borrow;
        return response()->json($success, 200); 
    }

    public function return(Request $request) 
    {
        $validator = $this->validator($request);
        if ($validator->fails()) return response()->json(['message' => __('main.msg_returm_book'), 'errors' => $validator->errors()], 400);
        
        $user   = User::find($request->user_id);
        $today  = Carbon::now()->toDateString();
        
        if($user == null) return response()->json(['message' => __('main.msg_not_found'),], 404);
        $borrow = $user->bookBorrowed()->where('book_code', $request->book_code)
                                        ->whereNull('return_date')
                                        ->first();
        
        if ($borrow == null) return response()->json(['message' => __('main.msg_not_borrow'), ], 200);

        $penalized = $this->penalized($request, $today);
        $borrow->update([
            'return_date' => $today,
        ]);

        $success['success']     = true;
        $success['penalized']   = $penalized;
        $success['message']     = __('main.msg_return_success');
        $success['error']       = '';
        $success['data']        = $borrow;
        return response()->json($success, 200); 
    }


    public function history(Request $request)
    {
        $validator = $this->validatorBook($request);
        if ($validator->fails()) return response()->json(['message' => __('main.msg_failed'), 'errors' => $validator->errors()], 400);

        $book = Book::where('code', $request->book_code)->first();
        if($book == null) return response()->json(['message' => __('main.msg_not_found'),], 404);

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['error']       = '';
        $success['data']        = $book->borrowHistory()->get();
        return response()->json($success, 200); 
    }
    
    public function penalized(Request $request, $today)
    {
        $user   = User::find($request->user_id);
        $borrow = $user->bookBorrowed()->where('book_code', $request->book_code)
                                            ->whereNull('return_date')
                                            ->first();

        $date_end = Carbon::parse($today)->addDays(7)->toDateString();
        if ($borrow->due_date < $today) {
            $user->update([
                'penalized_start'   => $today,
                'penalized_end'     => $date_end,
            ]);
            return __('main.msg_penalized_start');
        }
    }

    protected function validator(Request $request)
    {
        $rules = [
            'book_code' => ['required'],
            'user_id'   => ['required'],
        ];
        return Validator::make($request->all(), $rules);
    }

    protected function validatorBook(Request $request)
    {
        $rules = [
            'book_code' => ['required'],
        ];
        return Validator::make($request->all(), $rules);
    }
}
