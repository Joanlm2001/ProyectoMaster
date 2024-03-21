<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return '<h1>Hola Mundo</h1>';
});

Route::get('/welcome', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Rutas de la API
/*Metodos HTTP
    GET: obtener Datos o recursos
    POST: Guardar datos o hacer logica desde un form
    PUT: Actualizar datos
    DELETE: Eliminar datos o recursos
    */

//Rutas de preuba
Route::get('/user/prueba', 'App\Http\Controllers\UserController@pruebas');
Route::get('/pokemon-team/prueba', 'App\Http\Controllers\PokemonTeamController@pruebas');
Route::get('/user-favs/prueba', 'App\Http\Controllers\UserFavsController@pruebas');

//Rutas del controlador de usuarios
Route::post('/api/register', 'App\Http\Controllers\UserController@register');
Route::post('/api/login', 'App\Http\Controllers\UserController@login');
Route::put('/api/user/update', 'App\Http\Controllers\UserController@update');
Route::post('api/user/upload', 'App\Http\Controllers\UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/avatar/{filename}', 'App\Http\Controllers\UserController@getImg');
Route::get('api/user/detail/{id}', 'App\Http\Controllers\UserController@detail');
Route::get('/api/users', 'App\Http\Controllers\UserController@index');
Route::get('/api/user/{id}', 'App\Http\Controllers\UserController@show');
Route::put('api/user/update/{id}', 'App\Http\Controllers\UserController@updateUser');
Route::delete('api/user/delete/{id}', 'App\Http\Controllers\UserController@delete');
