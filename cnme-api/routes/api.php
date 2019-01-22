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


Route::apiResource('unidades', 'API\UnidadeController');

Route::post('unidades/{unidadeId}/add-localidade','API\UnidadeController@addLocalidade')
        ->name('unidade-addLocalidade');

Route::put('unidades/{unidadeId}/update-localidade','API\UnidadeController@updateLocalidade')
    ->name('unidade-updateLocalidade');

Route::get('unidades/{unidadeId}/usuarios','API\UnidadeController@usuarios')
    ->name('unidade-usuarios');

Route::apiResource('usuarios', 'API\UsuarioController');
Route::apiResource('tipounidades', 'API\TipoUnidadeController');
Route::apiResource('solicitacao-cnme', 'API\SolicitacaoProjetoController');
Route::apiResource('projeto-cnme', 'API\ProjetoController');

Route::apiResource('etapas', 'API\EtapaController');
Route::post('etapas/{etapaId}/add-tarefa','API\EtapaController@addTarefa')
    ->name('etapa-addTarefa');
Route::delete('etapas/{etapaId}/remove-tarefa/{tarefaId}','API\EtapaController@removeTarefa')
    ->name('etapa-removeTarefa');
Route::put('etapas/{etapaId}/update-tarefa/{tarefaId}','API\EtapaController@updateTarefa')
    ->name('etapa-updateTarefa');
Route::get('etapas/{etapaId}/tarefas','API\EtapaController@tarefas')
    ->name('etapa-tarefas');

Route::apiResource('tipoequipamentos', 'API\TipoEquipamentoController');



Route::get('localidades/estados', 'API\LocalidadeController@estados');
Route::get('localidades/estados/{uf}/municipios', 'API\LocalidadeController@municipios');
