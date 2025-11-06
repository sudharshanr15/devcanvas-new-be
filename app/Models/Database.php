<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Database extends Model
{
    protected $fillable = ["name", "user_id", "document_id"];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function collections(): HasMany{
        return $this->hasMany(Collection::class, "database_id", "id");
    }
}
