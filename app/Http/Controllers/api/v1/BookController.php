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
    /**
     * @OA\Get(
     *      path="/api/v1/book/list",
     *      tags={"Book"},
     *      summary="Get list of books",
     *      description="Returns list of books",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="search",
     *          description="Search by name or book code",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="text"
     *          )
     *      ),
     *     )
     */
    public function list(Request $request)
    {
        $book = Book::query();
        if ($request->search != null) {
            $book = $book->where('title', $request->search)
                            ->orWhere('code', $request->search);
        }

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['data']        = $book->get();
        return response()->json($success); 
    }

     /**
     * @OA\Post(
     *      path="/api/v1/book/borrow",
     *      tags={"Book"},
     *      summary="Borrow book",
     *      description="user borrow book",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Data tidak ditemukan",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\RequestBody(
    *           @OA\JsonContent(),
    *           @OA\MediaType(
    *               mediaType="multipart/form-data",
    *               @OA\Schema(
    *                   type="object",
    *                   required={"book_code", "user_id"},
    *                   @OA\Property(property="book_code", type="string"),
    *                   @OA\Property(property="user_id", type="integer")
    *               ),
    *           ),
     *      )
     * )
     */
    public function borrow(Request $request)
    {
        $book = Book::where('code', $request->book_code)->first();
        $user = User::find($request->user_id);
        
        $date       = Carbon::now()->toDateString();
        $due_date   = Carbon::parse($date)->addDays(7)->toDateString();
        
        if($user == null or $book == null) {
            $error['success']     = false;
            $error['message']     = __('main.msg_not_found');
            return response()->json([$error], 404);
        } 
            
        if ($user->is_penalized) {
            $error['success']     = false;
            $error['message']     = __('main.msg_penalized'). date('d M Y', strtotime($user->penalized_end));
            return response()->json([$error], 201);
        }

        if (!$user->allowBorrowing()) {
            $error['success']     = false;
            $error['message']     = __('main.msg_limit_borrow');
            return response()->json([$error], 201);
        }
        if ($book->available == 0){
            $error['success']     = false;
            $error['message']     = __('main.msg_not_avail');
            return response()->json([$error], 201);
        } 
        
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
        $success['data']        = $borrow;
        return response()->json($success, 200); 
    }

    /**
     * @OA\Put(
     *      path="/api/v1/book/return",
     *      tags={"Book"},
     *      summary="Return book",
     *      description="return book that borrowing",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Data tidak ditemukan",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="book_code",
     *          description="Search by name or book code",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          description="Search by name or book code",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * )
     */
    public function return(Request $request) 
    {
        $user   = User::find($request->user_id);
        $today  = Carbon::now()->toDateString();
        
        if($user == null) {
            $error['success']     = false;
            $error['message']     = __('main.msg_not_found');
            return response()->json([$error], 404);
        } 
        
        $borrow = $user->bookBorrowed()->where('book_code', $request->book_code)
                                        ->whereNull('return_date')
                                        ->first();
        
        if ($borrow == null) {
            $error['success']     = false;
            $error['message']     = __('main.msg_not_borrow');
            return response()->json([$error], 201);
        } 

        $penalized = $this->penalized($request, $today);
        $borrow->update([
            'return_date' => $today,
        ]);

        $success['success']     = true;
        $success['penalized']   = $penalized;
        $success['message']     = __('main.msg_return_success');
        $success['data']        = $borrow;
        return response()->json($success, 200); 
    }

    /**
     * @OA\Get(
     *      path="/api/v1/book/borrow/history",
     *      tags={"Book"},
     *      summary="Get list of hisroty user that borrow book",
     *      description="Returns list of user borrow ",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Data tidak ditemukan",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *       ),
     *      @OA\Parameter(
     *          name="code_book",
     *          description="Code Book for see history",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     )
     */
    public function history(Request $request)
    {
        $book = Book::where('code', $request->code_book)->first();
        if($book == null) {
            $error['success']     = false;
            $error['message']     = __('main.msg_not_found');
            return response()->json([$error], 404);
        }

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
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
}
