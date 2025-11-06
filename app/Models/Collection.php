<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = "collections";

    protected $fillable = [
        "document_id",
        "database_id",
        "name",
        "schema"
    ];

    protected $casts = [
        "schema" => "array",
    ];

    public function database(){
        return $this->belongsTo(Database::class, "database_id", "id");
    }
}
