<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    protected $fillable = ["name", "user_id", "document_id"];
}
