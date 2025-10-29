<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Models\StudentUser;
use Illuminate\Http\Request;

abstract class AbstractSessionController extends Controller{
    protected $guard = null;

    /**
     * Verify and authentication user credentials
     * 
     * @param Request $request
     */
    abstract public function store(Request $request);
    
    /**
     * 
     * Logout user and invalidate token
     * 
     */
    abstract public function destroy();

    public function refresh(){
        if($this->guard != null){
            $token = auth()->guard($this->guard)->refresh();
        }else{
            $token = auth()->refresh();
        }
        return $this->tokenResponse($token);
    }

    public function me(){
        return response()->json(auth()->guard($this->guard)->user());
    }

    /**
     * Generate token response
     * 
     * @param string $token
     */
    public function tokenResponse($token){
        return response()->json([
            "access_token" => $token,
            "token_type" => "bearer"
        ]);
    }
}