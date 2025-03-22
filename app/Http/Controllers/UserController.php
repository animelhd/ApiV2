<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

use App\Models\Anime;
use App\Models\User;
use App\Models\Codigo;

use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Mail\SendCodeRestorePassword;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * model instances.
     */
    protected $anime, $user;	

    /**
     * Create a new controller instance.
     *
     * @param  \App\Models\User;  $user
	 * @param Anime   $anime
     * @return void
     */	
	public function __construct(Anime $anime, User $user)
	{
		$this->user = $user;
		$this->anime = $anime;
	}
	
	
    /**
     * Api Login: Obtiene el token para la app.
     */
    public function getTokenApp(Request $request)
    {
        try {
            return $this->user->getTokenApp($request);
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    public function codePasswordRestore(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
            ], [
                'email.exists' => 'El correo electrónico no se encuentra en nuestros registros',
            ]);
            $user = User::where('email', $request->email)->first();

            $codigoExistente = Codigo::where('user_id', $user->id)->first();

            if ($codigoExistente && now() < Carbon::parse($codigoExistente->created_at)->addMinutes(5)) {
                throw new Exception("Ya se envió un código al correo electrónico o espera un momento para generar otro.", 401);
            } else {
                $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                Mail::to($user->email)->send(new SendCodeRestorePassword($codigo));
                if ($codigoExistente) {
                    $codigoExistente->delete();
                }
                $sendCode = new Codigo([
                    'codigo'     => $codigo,
                    'user_id'    => $user->id,
                    'expires_at' => now()->addMinutes(30),
                ]);
                $sendCode->save();
                $data = [
                    'status'     => true,
                    'message'    => 'El código ha sido enviado con éxito',
                    'user_id'    => $user->id,
                    'user_email' => $user->email,
                ];
                return response()->json($data, 200);
            }
        } catch (ValidationException $exception) {
            return response()->json([
                'status'  => false,
                'message' => 'El correo electrónico no se encuentra en nuestros registros',
                'errors'  => $exception->errors(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function passwordRestore(Request $request)
    {
        try {
            $request->validate([
                'user_id'    => 'required|exists:users,id',
                'user_email' => 'required|email|exists:users,email',
                'code'       => 'required|numeric|digits:6',
                'password'   => 'required|confirmed|min:8',
            ], [
                'user_id.exists'    => 'El usuario no se encuentra en nuestros registros',
                'user_email.exists' => 'El correo electrónico no se encuentra en nuestros registros',
            ]);

            $userCode = Codigo::where('user_id', $request->user_id)
                ->where('codigo', $request->code)
                ->first();

            if (!$userCode) {
                throw new Exception("El código ingresado no es correcto", 403);
            }

            if ($userCode->expires_at < now()) {
                throw new Exception("El código ha expirado", 403);
            }

            User::where('email', $request->user_email)
                ->update(['password' => Hash::make($request->password)]);

            $userCode->delete();

            $data = [
                'status'  => true,
                'message' => 'Se ha actualizado tu contraseña con éxito',
            ];

            return response()->json($data, 200);
        } catch (ValidationException $exception) {
            return response()->json([
                'status'  => false,
                'message' => 'Error',
                'errors'  => $exception->errors(),
            ], 422);
        } catch (Exception $e) {
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getRegister(Request $request)
    {
        try {
            return $this->user->getRegister($request);
        } catch (Exception $e) {
            return ['msg' => $e->getMessage()];
        }
    }

    public function logoutUser(Request $request)
    {
        try {
            return [
                'status' => $this->user->logoutUser($request),
                'code'   => 200,
                'msg'    => 'Desconectado',
            ];
        } catch (Exception $e) {
            return [
                'code' => 400,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function getUserData(Request $request)
    {
        try {
            return $this->user->getUserData($request);
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

	public function updateProfile(Request $request)
	{
		try {
			$name = $request->get('name');
			$image = $request->get('image');
			$namev = $this->user->where('name', $name)->first();
			$userUpdate = $this->user::find($request->id);
			if($userUpdate->name != $name) {
				if($namev) {
					return array(
						'code' => 400,
						'msg' => 'Este Username ya estan en uso'
					);
				} else {
					$userUpdate->name = $name;
					if($this->validateImage($image) && $image != null) {
						$userUpdate->image = $image;
					}else {
						$userUpdate->image = "https://avatarfiles.alphacoders.com/319/319952.jpg";
					}
					$userUpdate->save();
					return array(
						'status' => 'OK',
						'code' => 200,
						'data' => $userUpdate
					);					
				}
			} else {
				$userUpdate->name = $name;
				if($this->validateImage($image) && $image != null) {
					$userUpdate->image = $image;
				}else {
					$userUpdate->image = "https://avatarfiles.alphacoders.com/319/319952.jpg";
				}
				$userUpdate->save();
				return array(
					'status' => 'OK',
					'code' => 200,
					'data' => $userUpdate
				);
			}
		} catch (Exception $error) {
			return array(
				'status' => 'Error',
				'msg' => $error->getMessage(),
				'code' => $error->getCode(),
				'detailed' => $error->getLine()
			);
		}
	}
	
	public function validateImage($url){
		$extension = pathinfo($url, PATHINFO_EXTENSION);
		$extensionesImagen = array("jpg", "jpeg", "png", "gif", "bmp");
		if (in_array(strtolower($extension), $extensionesImagen)) {
			return true;
		} else {
			return false;
		}
	}	

	public function addAnimeFavorite(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->favorite($anime);
            return [
                'code' => 200,
                'msg'  => 'Se añadió a favoritos',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function deleteAnimeFavorite(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->unfavorite($anime);
            return [
                'code' => 200,
                'msg'  => 'Se eliminó de favoritos',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function listAnimeFavorite(Request $request)
    {
        try {
            $user = $this->user::find($request->user_id);
            $data = $user->getFavoriteItems(Anime::class)
                ->select('id', 'name', 'poster')
                ->orderBy('name', 'asc')
                ->get();
            return $data;
        } catch (Exception $e) {
            return ['status' => false];
        }
    }

    public function addAnimeView(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->view($anime);
            return [
                'code' => 200,
                'msg'  => 'Se añadió a finalizados',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function deleteAnimeView(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->unview($anime);
            return [
                'code' => 200,
                'msg'  => 'Se eliminó de finalizados',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function listAnimeView(Request $request)
    {
        try {
            $user = $this->user::find($request->user_id);
            $data = $user->getViewItems(Anime::class)
                ->select('id', 'name', 'poster')
                ->orderBy('name', 'asc')
                ->get();
            return $data;
        } catch (Exception $e) {
            return ['status' => false];
        }
    }

    public function addAnimeWatching(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->watching($anime);
            return [
                'code' => 200,
                'msg'  => 'Agregado a Viendo',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function deleteAnimeWatching(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->unwatching($anime);
            return [
                'code' => 200,
                'msg'  => 'Eliminado de Viendo',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function listAnimeWatching(Request $request)
    {
        try {
            $user = $this->user::find($request->user_id);
            $data = $user->getWatchingItems(Anime::class)
                ->select('id', 'name', 'poster')
                ->orderBy('name', 'asc')
                ->get();
            return $data;
        } catch (Exception $e) {
            return ['status' => false];
        }
    }	
	
    public function addAnimeWatchlater(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->Watchlater($anime);
            return [
                'code' => 200,
                'msg'  => 'Agregado a Ver Después',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function deleteAnimeWatchlater(Request $request)
    {
        try {
            $user  = $this->user::find($request->user_id);
            $anime = $this->anime::find($request->anime_id);
            $user->unWatchlater($anime);
            return [
                'code' => 200,
                'msg'  => 'Eliminado de Ver Después',
            ];
        } catch (Exception $e) {
            return [
                'code' => 404,
                'msg'  => $e->getMessage(),
            ];
        }
    }

    public function listAnimeWatchlater(Request $request)
    {
        try {
            $user = $this->user::find($request->user_id);
            $data = $user->getWatchlaterItems(Anime::class)
                ->select('id', 'name', 'poster')
                ->orderBy('name', 'asc')
                ->get();
            return $data;
        } catch (Exception $e) {
            return ['status' => false];
        }
    }	
}