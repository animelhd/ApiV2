<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Server extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Obtiene la lista de servidores.
     *
     * @return Collection
     */
    public function getServersList(): Collection
    {
        return $this->select('id', 'title as name', 'position', 'embed', 'status')
            ->get();
    }
}