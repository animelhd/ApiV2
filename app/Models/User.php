<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Animelhd\AnimesFavorite\Traits\Favoriter;
use Animelhd\AnimesView\Traits\Viewer;
use Animelhd\AnimesWatching\Traits\Watchinger;
use Animelhd\AnimesWatchlater\Traits\Watchlaterer;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
	use Favoriter, Viewer, Watchinger, Watchlaterer;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
	
    /**
     * Obtiene un token para la app, validando si el usuario es premium.
     *
     * @param Request $request
     */
    public function getTokenApp(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'device_name' => 'required',
        ]);

        $user = $this->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return [
                'code' => 400,
                'msg'  => 'Usuario y/o contraseña incorrectos.'
            ];
        }

        if ($user->isPremium) {
            if ($user->tokens()->exists()) {
                $user->tokens()->delete();
            }
        }

        return [
            'code'  => 200,
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user'  => $user,
        ];
    }

    /**
     * Registra un nuevo usuario.
     *
     * @param Request $request
     */
    public function getRegister(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255',
            'password' => 'required|string|confirmed|min:7',
        ]);

        $emailExists = $this->where('email', $request->email)->exists();
        $nameExists  = $this->where('name', $request->name)->exists();

        if ($emailExists && $nameExists) {
            return [
                'code' => 400,
                'msg'  => 'Este Email y Username ya están en uso'
            ];
        } elseif ($emailExists) {
            return [
                'code' => 400,
                'msg'  => 'Este Email ya está en uso'
            ];
        } elseif ($nameExists) {
            return [
                'code' => 400,
                'msg'  => 'Este Username ya está en uso'
            ];
        }

        self::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return [
            'code' => 200,
            'msg'  => 'Registro Exitoso'
        ];
    }
	
	 /**
     * Cierra la sesión del usuario autenticado.
     *
     * @param Request $request
     */
    public function logoutUser(Request $request)
    {
        return (bool)$request->user()->currentAccessToken()->delete();
    }
	
    /**
     * Retorna los datos del usuario autenticado.
     *
     * @param Request $request
     */
    public function getUserData(Request $request)
    {
        $user = $request->user();
        return [
            'code' => 200,
            'user' => $user,
        ];
    }	
}
