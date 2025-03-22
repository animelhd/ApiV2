<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Codigo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'user_id',
        'expires_at',
    ];

    protected $dates = [
        'created_at',
        'expires_at',
    ];

    /**
     * Relación con el usuario.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}