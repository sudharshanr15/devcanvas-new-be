<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\HttpResponseCode;
use App\Models\Database;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{

    use ApiResponse;

    // display the list of resources
    public function index(){
        // TODO: get only databases for a user
        $databases = Database::all();

        return view("databases.index", compact("databases"));
    }

    public function show(){
        $user = auth()->user();

        $database = $user->databases;

        return $this->successResponse($database);

    }

    // store the new resource
    public function store(Request $request){
        $user_id = auth()->user()->id;

        $attributes = $request->validate([
            "name" => ["required", "min:3", "unique:databases,name"]
        ]);

        $database_name = $request->name;
        $db = DB::connection();

        try{
            $db->getPdo();
            $db->statement("CREATE DATABASE " . $database_name);
        }catch(Exception $e){
            return $this->errorResponse("Unable to create database", null, HttpResponseCode::INTERNAL_SERVER_ERROR);
        }

        // TODO: get user_id from current authenticated user
        $user_id = $user_id;
        $attributes["user_id"] = $user_id;
        $attributes["document_id"] = md5($user_id . time());

        $database = Database::create($attributes);
        if($database){
            return $this->successResponse($attributes, "Success", HttpResponseCode::CREATED);
        }else{
            return $this->errorResponse("Unable to create database", null, HttpResponseCode::INTERNAL_SERVER_ERROR);
        }

        return ;
    }
}
