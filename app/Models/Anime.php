<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Cviebrock\EloquentSluggable\Sluggable;
use Animelhd\AnimesFavorite\Traits\Favoriteable;
use Animelhd\AnimesView\Traits\Vieweable;
use Animelhd\AnimesWatching\Traits\Watchingable;
use Animelhd\AnimesWatchlater\Traits\Watchlaterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class Anime extends Model
{
    use Sluggable, Favoriteable, Vieweable, Watchingable, Watchlaterable;

    /**
     * Atributos no asignables masivamente.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Los atributos que se deben convertir a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Los atributos que deben ser tratados como instancias de fecha.
     *
     * @var array
     */
    protected $dates = [
        'aired',
    ];

    /**
     * Configuración del sluggable.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ],
        ];
    }

    /**
     * Relación con los episodios.
     *
     * @return HasMany
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(\App\Models\Episode::class);
    }

    // Version 1.1.0
    public function getNewAnimes2(object $request)
    {
        return $this
            ->select([
                'id','name','name_alternative','banner','poster','overview','aired','type','status','genres','rating',
                'vote_average','premiered as season','broadcast','prequel','sequel','related','views_app as visitas','updated_at'
            ])
            ->where('id', '>=', 1)
            // ->where('id', '>=', $request->id_anime)
            ->where('id', '<=', 1)
            ->orderBy('aired', 'desc')
            ->get();
    }

    // Version 1.2.0
    public function getNewAnimes(object $request)
    {
		return $this
			->select([
				'id','name','name_alternative','banner','poster','overview','aired','type','status','genres','rating',
				'vote_average','premiered as season','broadcast','prequel','sequel','related','views_app as visitas','updated_at'
			])
			->where(function ($query) use ($request) {
				$query->whereBetween('id', [$request->id_anime, 1762])
					  ->orWhereIn('id', [1482, 1496, 1514, 1517, 1566, 1568, 1575, 1576, 1588, 1649, 1577, 1580, 1582, 1583]);
			})
			->orderBy('aired', 'desc')
			->get();
    }
}