<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Player;
use App\Models\Server;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    /**
     * Instancias de modelos.
     */
    protected Episode $episode;
    protected Anime $anime;
    protected Player $player;
    protected Server $server;
    protected User $user;

    /**
     * Crea una nueva instancia del controlador.
     *
     * @param Episode $episode
     * @param Anime   $anime
     * @param Player  $player
     * @param Server  $server
     * @param User    $user
     */
    public function __construct(Episode $episode, Anime $anime, Player $player, Server $server, User $user)
    {
        $this->episode = $episode;
        $this->anime   = $anime;
        $this->player  = $player;
        $this->server  = $server;
        $this->user    = $user;
    }
	
    public function setViewsAnime(Request $request)
    {
        try {
            DB::unprepared('update episodes set views_app = views_app+1 where id = ' . $request->episode_id);
            return ['status' => true];
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }
	
	public function setViewsAnimes(Request $request)
	{
		try {
			// Iniciamos una transacción para asegurar que la operación se realice de forma atómica.
			DB::beginTransaction();

			// Actualizamos la columna views_app en animes con la suma de views_app de sus episodios
			DB::update(
				"UPDATE animes 
				 SET views_app = (
					SELECT COALESCE(SUM(episodes.views_app), 0)
					FROM episodes 
					WHERE episodes.anime_id = animes.id
				 )
				 WHERE animes.id = ?",
				[$request->id]
			);

			DB::commit();

			return ['status' => true];
		} catch (Exception $e) {
			DB::rollBack();
			return ['msg' => $e->getMessage()];
		}
	}	
	
    public function addReportPlayer(Request $request)
    {
        try {
            $data = $this->player::select(
                    'animes.name as anime',
                    'episodes.number',
                    'servers.title as server',
                    'players.id as player_id'
                )
                ->where('players.id', $request->player_id)
                ->join('episodes', 'episodes.id', '=', 'players.episode_id')
                ->join('animes', 'animes.id', '=', 'episodes.anime_id')
                ->join('servers', 'servers.id', '=', 'players.server_id')
                ->first();

            $server = strtolower($data->server);
            if ($server !== "dseta") {
                if (!DB::table('reportes')->where('player_id', $request->player_id)->exists()) {
                    DB::table('reportes')->insert($data->toArray());
                }
                return [
                    'code'   => 200,
                    'status' => true,
                    'data'   => $data,
                ];
            } else {
                return [
                    'code'   => 200,
                    'status' => false,
                    'data'   => $data,
                ];
            }
        } catch (Exception $e) {
            return [
                'code'   => 400,
                'status' => false,
            ];
        }
    }

    /**
     * Obtiene el último player.
     *
     * @return array
     */
    public function getLastPlayer()
    {
        try {
            return $this->player->getLastPlayer();
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    /**
     * @param Request $request
     */
    public function getRecentApp2(Request $request)
    {
        try {
            return [
                'animes'   => $this->anime->getNewAnimes2($request),
                'episodes' => $this->episode->getNewEpisodes2($request),
                'servers'  => $this->server->getServersList(),
                'players'  => $this->player->getNewPlayers2($request)
            ];
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    /**
     * Obtiene datos recientes para la versión 1.2.0 de la app.
     *
     * @param Request $request
     */
    public function getRecentApp(Request $request)
    {
        try {
            return [
                'animes'   => $this->anime->getNewAnimes($request),
                'episodes' => $this->episode->getNewEpisodes($request),
                'servers'  => $this->server->getServersList(),
                'players'  => $this->player->getNewPlayers($request)
            ];
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }
}