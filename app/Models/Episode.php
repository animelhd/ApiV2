<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    /**
     * Atributos no asignables masivamente.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;
    public $updated_at = true;

    /**
     * Conversión de atributos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'id'       => 'integer',
        'anime_id' => 'integer',
    ];

    /**
     * Relación con los players.
     *
     * @return HasMany
     */
    public function players(): HasMany
    {
        return $this->hasMany(\App\Models\Player::class);
    }

    /**
     * Relación con el anime.
     *
     * @return BelongsTo
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Anime::class);
    }

    //Version 1.0.4: Obtiene nuevos episodios.
    public function getNewEpisodes2(object $request)
    {
        return $this
            ->select('id', 'number', 'anime_id', 'created_at')
            ->where('id', '>=', 1)
            // ->where('id', '>=', $request->id_episode)
            ->where('id', '<=', 1)
            ->orderBy('episodes.id', 'desc')
            ->get();
    }

    //Version 1.2.0: Obtiene nuevos episodios, incluyendo casos especiales.
    public function getNewEpisodes(object $request)
    {
        return $this
            ->select('id', 'number', 'anime_id', 'created_at')
            ->where('id', '>=', $request->id_episode)
            ->where('id', '<=', 28748)
            ->orderBy('episodes.id', 'desc')
            ->get();
    }
}