<?php

use Illuminate\Http\Request;
use App\User;
use App\Models\Unidade;
use App\Http\Resources\UserResource;
use App\Http\Resources\UnidadeResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::get('unidades/{unidadeId}/usuarios', function() {
//     //return new UserResource(User::find(1));;
//     return UserResource::collection(User::paginate());
// });

// Route::get('/unidades/{id}', function() {
//     return new UnidadeResource(Unidade::find(3));
// });

Route::apiResource('unidades', 'API\UnidadeController');

Route::post('unidades/{unidadeId}/add-localidade','API\UnidadeController@addLocalidade')
        ->name('unidade-addLocalidade');

Route::post('unidades/{unidadeId}/update-localidade','API\UnidadeController@updateLocalidade')
    ->name('unidade-updateLocalidade');

Route::get('unidades/{unidadeId}/usuarios','API\UnidadeController@usuarios')
    ->name('unidade-usuarios');

Route::apiResource('usuarios', 'API\UsuarioController');


Route::get('localidades/estados', 'API\LocalidadeController@estados');
Route::get('localidades/estados/{uf}/municipios', 'API\LocalidadeController@municipios');
