<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use DomainException;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use UnexpectedValueException;

class JwtAuth
{

    public $key;

    public function __construct()
    {
        $this->key = 'clave_segura123456123';
    }

    public function signUp($email, $password, $getToken = null)
    {
        //Buscar usuario por credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        //Comporbar is son correctas
        $signUp = false;
        if (is_object($user)) {
            $signUp = true;
        }

        //Generar el token
        if ($signUp) {

            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (365 * 24 * 60 * 60),
                'role' => $user->role
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Devolver los datos decodificados o el token
            if (is_null($getToken)) {
                $data =  $jwt;
            } else {
                $data =  $decoded;
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }
        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (UnexpectedValueException $e) {
            $auth = false;
        } catch (DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }
        $auth = true;

        return $auth;
    }
}
