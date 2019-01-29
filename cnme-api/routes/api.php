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








Route::post('projeto-cnme/{projetoId}/add-equipamento/{equipamentoId}','API\ProjetoController@addEquipamento');
Route::post('projeto-cnme/{projetoId}/add-equipamentos','API\ProjetoController@addEquipamentoList');
Route::delete('projeto-cnme/{projetoId}/remove-equipamento/{equipamentoProjetoId}','API\ProjetoController@removeEquipamento');

Route::post('projeto-cnme/{projetoId}/add-kit/{kitId}','API\ProjetoController@addKit');
Route::delete('projeto-cnme/{projetoId}/remove-kit/{kitId}','API\ProjetoController@removeKit');







Route::apiResource('etapas', 'API\EtapaController');
Route::post('etapas/{etapaId}/add-tarefa','API\EtapaController@addTarefa')
    ->name('etapa-addTarefa');
Route::delete('etapas/{etapaId}/remove-tarefa/{tarefaId}','API\EtapaController@removeTarefa')
    ->name('etapa-removeTarefa');
Route::put('etapas/{etapaId}/update-tarefa/{tarefaId}','API\EtapaController@updateTarefa')
    ->name('etapa-updateTarefa');
Route::get('etapas/{etapaId}/tarefas','API\EtapaController@tarefas')
    ->name('etapa-tarefas');
    Route::get('etapas/{etapaId}/equipamentos','API\EtapaController@equipamentos')
    ->name('etapa-equipamentos');




Route::apiResource('tipoequipamentos', 'API\TipoEquipamentoController');
Route::apiResource('equipamentos', 'API\EquipamentoController');







Route::apiResource('kits', 'API\KitController');
Route::post('kits/{kitId}/add-equipamento/{equipamentoId}', 'API\KitController@addEquipamento');
Route::delete('kits/{kitId}/remove-equipamento/{equipamentoId}', 'API\KitController@removeEquipamento');




Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-kit','API\TarefaController@addKitAll');
Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento-ids','API\TarefaController@syncEquipamentosProjeto');
Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento/{equipamentoProjetoId}','API\TarefaController@addEquipamentoProjeto');
Route::delete('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamento/{equipamentoProjetoId}','API\TarefaController@removeEquipamentoProjeto');
Route::delete('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamentos','API\TarefaController@clearEquipamentoProjeto');





Route::apiResource('checklists', 'API\ChecklistController');
Route::post('checklists/{checklistId}/add-itemchecklist', 'API\ChecklistController@addItemChecklist');
Route::delete('checklists/{checklistId}/remove-itemchecklist/{itemId}', 'API\ChecklistController@removeItemChecklist');






Route::get('localidades/estados', 'API\LocalidadeController@estados');
Route::get('localidades/estados/{uf}/municipios', 'API\LocalidadeController@municipios');
