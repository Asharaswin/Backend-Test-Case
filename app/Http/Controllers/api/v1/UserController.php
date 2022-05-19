<?php

namespace App\Http\Controllers\api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
     /**
     * @OA\Get(
     *      path="/api/v1/user/list",
     *      tags={"User"},
     *      summary="Get list of users",
     *      description="Returns list of users",
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
     *          name="search",
     *          description="Search by id or name",
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
        $user = User::query();
        if ($request->search != null) {
            $user = $user->where('id', $request->search)
                            ->orWhere('name', 'like', '%'. $request->search.'%');
        }
        
        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['error']       = '';
        $success['data']        = $user->get();
        return response()->json($success, 200); 
    }

    /**
     * @OA\Get(
     *      path="/api/v1/user/list/borrowing",
     *      tags={"User"},
     *      summary="Get list of Book that user borrowing",
     *      description="Returns list of Book that user borrowing",
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
     *          name="user_id",
     *          description="User Id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     )
     */
    public function borrowing(Request $request)
    {
        $user = User::find($request->user_id);
        if($user == null) {
            $error['success']     = false;
            $error['message']     = __('main.msg_not_found');
            return response()->json([$error], 404);
        }

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['error']       = '';
        $success['data']        = $user->bookBorrowed()->whereNull('return_date')->get();
        return response()->json($success, 200); 
    }
    
    /**
     * @OA\Get(
     *      path="/api/v1/user/borrow/history",
     *      tags={"User"},
     *      summary="Get list Book Borrow History of user",
     *      description="Returns list of Book borrow history that user borrow",
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
     *          name="user_id",
     *          description="User Id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     )
     */
    public function history(Request $request)
    {
        $user = User::find($request->user_id);
        
        if($user == null) {
            $error['success']     = false;
            $error['message']     = __('main.msg_not_found');
            return response()->json([$error], 404);
        }

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['data']        = $user->bookBorrowed()->whereNotNull('return_date')->get();
        return response()->json($success, 200); 
    }
}
