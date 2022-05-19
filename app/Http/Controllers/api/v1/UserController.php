<?php

namespace App\Http\Controllers\api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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

    public function borrowing(Request $request)
    {
        $validator = $this->validator($request);
        if ($validator->fails()) return response()->json(['message' => __('main.msg_failed'), 'errors' => $validator->errors()], 400);
        
        $user = User::find($request->user_id);
        if($user == null) return response()->json(['message.id_not_found', ], 404);

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['error']       = '';
        $success['data']        = $user->bookBorrowed()->whereNull('return_date')->get();
        return response()->json($success, 200); 
    }
    
    public function history(Request $request)
    {
        $validator = $this->validator($request);
        if ($validator->fails()) return response()->json(['message' => __('main.msg_failed'), 'errors' => $validator->errors()], 400);
        
        $user = User::find($request->user_id);
        if($user == null) return response()->json(['message.id_not_found', ], 404);

        $success['success']     = true;
        $success['message']     = __('main.msg_return_data');
        $success['error']       = '';
        $success['data']        = $user->bookBorrowed()->whereNotNull('return_date')->get();
        return response()->json($success, 200); 
    }

    protected function validator(Request $request)
    {
        $rules = [
            'user_id'   => ['required'],
        ];
        return Validator::make($request->all(), $rules);
    } 
}
