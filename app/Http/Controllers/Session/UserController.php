<?php

namespace App\Http\Controllers\Session;

use App\Helpers\ApiResponse;
use App\Helpers\HttpResponseCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends AbstractSessionController
{
    use ApiResponse;

    public function show()
    {
        $user = auth()->user();
        if(!$user){
            return $this->errorResponse("User not authenticated", null, HttpResponseCode::UNAUTHORIZED);
        }
        return $this->successResponse($user);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required"]
        ]);

        $token = Auth::attempt($attributes);
        if(!$token){
            throw ValidationException::withMessages([]);
        }
        
        return $this->tokenResponse($token);
    }

    /**
     * @param User $user 
     */
    public function destroy()
    {
        $user = auth()->user();
        if(!$user){
            return $this->errorResponse("User not authenticated", null, HttpResponseCode::UNAUTHORIZED);
        }
        auth()->logout();
        return $this->successResponse(null, "Successfully logged out", HttpResponseCode::OK);
    }
}
