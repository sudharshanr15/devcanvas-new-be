<?php

namespace App\Http\Controllers;

use App\Models\Database;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    // display the list of resources
    public function index(){
        // TODO: get only databases for a user
        $databases = Database::all();

        return view("databases.index", compact("databases"));
    }

    // store the new resource
    public function store(Request $request){
        $attributes = $request->validate([
            "name" => ["required", "min:3"]
        ]);

        // TODO: get user_id from current authenticated user
        $user_id = 1;
        $attributes["user_id"] = $user_id;
        $attributes["document_id"] = md5($user_id . time());

        Database::create($attributes);

        return redirect()->route("database.index");
    }
}
