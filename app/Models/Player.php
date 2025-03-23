<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Player extends Model
{
    protected $guarded = [];

    /**
     * Los atributos que se deben convertir a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'id'         => 'integer',
        'server_id'  => 'integer',
        'episode_id' => 'integer',
    ];

    /**
     * Relación con el server.
     *
     * @return BelongsTo
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Server::class);
    }

    /**
     * Relación con el episodio.
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Episode::class);
    }

    /**
     * Versión 1.0.4: Obtiene nuevos players dentro de un rango de episodios.
     *
     * @param object $request
     * @return Collection
     */
    public function getNewPlayers2(object $request): Collection
    {
        return $this
            ->select('players.id', 'code as link', 'languaje as language', 'server_id', 'episode_id', 'players.updated_at')
            ->where('episodes.id', '>=', 25001)
            ->where('episodes.id', '<=', 30000)
            ->join('episodes', 'episodes.id', '=', 'players.episode_id')
            ->join('animes', 'animes.id', '=', 'episodes.anime_id')
            ->join('servers', 'servers.id', '=', 'players.server_id')
            ->orderBy('episode_id', 'desc')
            ->orderBy('players.id', 'desc')
            ->get();
    }

    /**
     * Versión 1.2.0: Obtiene el último player según ciertas condiciones.
     *
     * @return self|null
     */
	public function getLastPlayer()
	{
		return response()->json(['id' => 117650]);
	}

    /**
     * Obtiene nuevos players con condiciones específicas.
     *
     * @param object $request
     * @return Collection
     */
    public function getNewPlayers(object $request): Collection
    {
        return $this
            ->select('players.id', 'code as link', 'languaje as language', 'server_id', 'episode_id', 'players.updated_at')
            ->join('episodes', 'episodes.id', '=', 'players.episode_id')
            ->join('animes', 'animes.id', '=', 'episodes.anime_id')
            ->join('servers', 'servers.id', '=', 'players.server_id')
            ->where('players.id', '>=', $request->id_player)
            ->where('players.id', '<=', 117650)
            ->where(function ($q) {
                $q->whereNotIn('servers.id', [3, 4])
                  ->orWhere('animes.id', '>', '1475');
            })
            ->orWhere('players.id', '>=', 117379)
            ->where('players.id', '<=', 117382)
            ->orderBy('episode_id', 'desc')
            ->orderBy('players.id', 'desc')
            ->get();
    }
}