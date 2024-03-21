<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\User;
use App\Models\UserFavs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\HTTP\Response;

class UserController extends Controller
{

    public function index()
    {

        $users = User::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'users' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (is_object($user)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El Usuario no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function pruebas(Request $request)
    {
        return "Accion User Controler";
    }

    public function register(Request $request)
    {

        //Recoger datos usuario
        $json = $request->input('json', null);
        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); //array

        //Limpiar datos
        if (!empty($params) && !empty($params_array)) {

            $params_array = array_map('trim', $params_array);
            //Validar datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:user',
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                //La validacion ha fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors(),
                );
            } else {
                //Validacion pasada correctamente

                //Cifrar contraseña
                $pwd = hash('sha256', $params->password);

                //Crear usuario

                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                //Guardar el usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario  se ha creado',
                    'user' => $user,
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos',
            );
        }

        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new  JwtAuth();

        //Recibir datos
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //Validar datos
        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            //La validacion ha fallado
            $signUp = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors(),
            );
        } else {
            //Cifrar Contraseña
            $pwd = hash('sha256', $params->password);

            $signUp = $jwtAuth->signUp($params->email, $pwd);

            //Devolver token o datos
            if (!empty($params->gettoken)) {
                $signUp = $jwtAuth->signUp($params->email, $pwd, true);
            }
        }
        return  response()->json($signUp, 200);
    }

    public function update(Request $request)
    {
        // Verificar si el usuario tiene el rol USER_ADMIN
        $isAdmin = false;
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth;
        if ($token) {
            $checkToken = $jwtAuth->checkToken($token);
            if ($checkToken) {
                $user = $jwtAuth->checkToken($token, true);
                if ($user->role == 'USER_ADMIN') {
                    $isAdmin = true;
                }
            }
        }

        // Recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($isAdmin || ($checkToken && !empty($params_array))) {
            // Sacar usuario identificado
            if (!$isAdmin) {
                $user = $jwtAuth->checkToken($token, true);
            }

            // Validar datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:user,email,' . $user->sub
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error en la validación de datos',
                    'errors' => $validate->errors()
                );
                return response()->json($data, $data['code']);
            }

            // Quitar campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['updated_at']);

            // Actualizar usuario en bbdd
            $user_update = User::where('id', $user->sub)->update($params_array);

            // Devolver array con resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                // Para mostrar los datos nuevos
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado desde UserController o no tiene permisos de administrador'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function updateUser(Request $request, $id)
    {
        // Verificar si el usuario tiene el rol USER_ADMIN
        $isAdmin = false;
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth;
        if ($token) {
            $checkToken = $jwtAuth->checkToken($token);
            if ($checkToken) {
                $user = $jwtAuth->checkToken($token, true);
                if ($user->role == 'USER_ADMIN') {
                    $isAdmin = true;
                }
            }
        }

        // Recoger datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($isAdmin || ($checkToken && !empty($params_array))) {
            // Validar datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:user,email,' . $id
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error en la validación de datos',
                    'errors' => $validate->errors()
                );
                return response()->json($data, $data['code']);
            }

            // Quitar campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['updated_at']);

            // Actualizar usuario en bbdd
            $user_update = User::where('id', $id)->update($params_array);

            // Devolver array con resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user_update,
                // Para mostrar los datos nuevos
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado desde UserController o no tiene permisos de administrador'
            );
        }

        return response()->json($data, $data['code']);
    }





    public function upload(Request $request)
    {

        //Recoger los datos de la peticion
        $img = $request->file('file0');

        //Validar Imagen
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        //Guardar Imagen

        if (!$img || $validate->fails()) {

            $data = array(
                'code' => 400,
                'status' => 'success',
                'message' => 'Error al subir imagen'
            );
        } else {
            $image_name = time() . $img->getClientOriginalName();
            Storage::disk('users')->put($image_name, File::get($img));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }
        return response()->json($data, $data['code']);
    }

    public function getImg($filename)
    {
        $isset = Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe',
            );
        }

        return response()->json($data, $data['code']);
    }

    public function teams()
    {
        return $this->hasMany(UserFavs::class);
    }

    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            );
        } else {
            $user->delete();
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Usuario eliminado correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }
}
