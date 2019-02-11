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

/**
 * ###############################################################################################################
 */

/**
 * API unidades
 * --
 * 
 * API      /api/tipounidades
 * API      /api/unidades
 * POST     /api/unidades/?/add-localidade              * Add localidade posteriormente a unidade
 * PUT      /api/unidades/?/update-localidade           
 * GET      /api/unidades/?/usuarios                   
 * GET      /api/unidades/u/pesquisar                   * Pesquisa por nome e/ou inep usando o parâmetro q
 * 
 * GET      /api/unidades/check-email-disponivel/{email}
 * GET      /api/unidades/check-inep-disponivel/{inep}
 * 
 * GET      /api/unidades/u/mec/
 * GET      /api/unidades/u/tvescola/
 * GET      /api/unidades/u/gestoras/
 * GET      /api/unidades/u/polos/
 * * GET    /api/unidades/u/polos/novos               * Polos sem projeto iniciado
 * GET      /api/unidades/u/empresas/
 */

Route::get('localidades/estados', 'API\LocalidadeController@estados');
Route::get('localidades/estados/{uf}/municipios', 'API\LocalidadeController@municipios');

Route::apiResource('tipounidades', 'API\TipoUnidadeController');

Route::apiResource('unidades', 'API\UnidadeController');
Route::post('unidades/{unidadeId}/add-localidade','API\UnidadeController@addLocalidade')
        ->name('unidade-addLocalidade');
Route::put('unidades/{unidadeId}/update-localidade','API\UnidadeController@updateLocalidade')
    ->name('unidade-updateLocalidade');
Route::get('unidades/{unidadeId}/usuarios','API\UnidadeController@usuarios')
    ->name('unidade-usuarios');
Route::get('unidades/u/pesquisar','API\UnidadeController@search');

Route::get('unidades/check-email-disponivel/{email}','API\UnidadeController@checkEmail');
Route::get('unidades/check-inep-disponivel/{inep}','API\UnidadeController@checkInep');

Route::get('unidades/u/mec/','API\UnidadeController@mec');
Route::get('unidades/u/tvescola/','API\UnidadeController@tvescola');
Route::get('unidades/u/gestoras/','API\UnidadeController@gestoras');
Route::get('unidades/u/polos/','API\UnidadeController@polos');
Route::get('unidades/u/empresas/','API\UnidadeController@empresas');

Route::get('unidades/u/polos/novos','API\UnidadeController@polosNovos');



/**
 * ################################################################################################################
 */

/** USUÁRIOS
 * API      /api/usuarios
 * GET      /usuarios/u/pesquisar                                      * Pesquisa por nome, cpf e email com o parâmetro q
 * GET      usuarios/check-email-disponivel/{email}                    * Verifica se o email está disponível
 * GET      usuarios/check-cpf-disponivel/{cpf}                        * Verifica se o cpf está disponível
 */

Route::apiResource('usuarios', 'API\UsuarioController');
Route::get('usuarios/u/pesquisar','API\UsuarioController@search');
Route::get('usuarios/check-email-disponivel/{email}','API\UsuarioController@checkEmail');
Route::get('usuarios/check-cpf-disponivel/{cpf}','API\UsuarioController@checkCpf');


/**Funcionalidade removida do escopo do projeto */
//Route::apiResource('solicitacao-cnme', 'API\SolicitacaoProjetoController');

/**################################################################################################################ */

/** EQUIPAMENTOS E TIPOS
 * API      /api/tipoequipamentos
 * API      /api/equipamentos
 * API      /api/equipamentos/e/pesquisar                               * Pesquisa por nome do equipamento com q, e/ou tipo do equipamento por tipo(string)
 */
Route::apiResource('tipoequipamentos', 'API\TipoEquipamentoController');
Route::apiResource('equipamentos', 'API\EquipamentoController');
Route::get('equipamentos/e/pesquisar','API\EquipamentoController@search');

/**
 * 
 * API      /api/kits
 * POST     /api/kits/{kitId}/add-equipamento/{equipamentoId}           * Adiciona um equimamento específico ao kit
 * POST     /api/kits/{kitId}/add-equipamentos                          * Adiciona uma lista de equimamentos enviada no body no campo ids. Ex.: [2,3,4,5,6,7]
 * DELETE   /api/kits/{kitId}/remove-equipamento/{equipamentoId}        * Remove um equipamento específico
 */
Route::apiResource('kits', 'API\KitController');
Route::get('kits/{kitId}/diffKit/equipamentos','API\KitController@diffKit');
Route::post('kits/{kitId}/add-equipamento/{equipamentoId}', 'API\KitController@addEquipamento');
Route::post('kits/{kitId}/add-equipamentos', 'API\KitController@addEquipamentoList');
Route::delete('kits/{kitId}/remove-equipamento/{equipamentoId}', 'API\KitController@removeEquipamento');



/**
 * 
 * API              /api/projeto-cnme
 * POST             /api/projeto-cnme/{projetoId}/add-equipamento/{equipamentoId}
 * POST             /api/projeto-cnme/{projetoId}/add-equipamentos                              * Adiciona uma lista de equimamentos enviada no body no campo ids. Ex.: [2,3,4,5,6,7]
 * DELETE           /api/projeto-cnme/{projetoId}/remove-equipamento/{equipamentoId}            * Remove um equipamento específico, 
 * POST             /api/projeto-cnme/{projetoId}/add-kit/{kitId}                               * Adiciona todos os equipamentos de um dado kit ao projeto
 * DELETE           /api/projeto-cnme/{projetoId}/remove-kit/{kitId}                            * Remove todos os equipamentos do kit que estão no projeto
 * GET              /api/projeto-cnme/p/pesquisar                                               * Pesquisar por 
 *                                                                                                  q(descricao do projeto, nome da unidade), 
 *                                                                                                  status("PLANEJAMENTO",ENVIO,INSTALACAO,ATIVACAO,
 *                                                                                                          FINALIZADO,CANCELADO)
 *                                                                                                  atrasados
 */

Route::apiResource('projeto-cnme', 'API\ProjetoController');
Route::get('projeto-cnme/p/status','API\ProjetoController@status');

Route::post('projeto-cnme/criar','API\ProjetoController@store');
Route::post('projeto-cnme/{projetoId}/add-equipamento/{equipamentoId}','API\ProjetoController@addEquipamento');
Route::post('projeto-cnme/{projetoId}/add-equipamentos','API\ProjetoController@addEquipamentoList');
Route::delete('projeto-cnme/{projetoId}/remove-equipamento/{equipamentoProjetoId}','API\ProjetoController@removeEquipamento');
Route::post('projeto-cnme/{projetoId}/add-kit/{kitId}','API\ProjetoController@addKit');
Route::delete('projeto-cnme/{projetoId}/remove-kit/{kitId}','API\ProjetoController@removeKit');
Route::get('projeto-cnme/p/pesquisar', 'API\ProjetoController@search');
Route::get('projeto-cnme/p/atrasados', 'API\ProjetoController@atrasados');





Route::apiResource('etapas', 'API\EtapaController');
Route::get('etapas/e/status','API\EtapaController@status');
Route::get('etapas/e/tipos','API\EtapaController@tipos');
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


/**
 * POST     /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-kit                                        * Adiciona na tarefa todos os equipamentos do projeto naquela tarefa
 * POST     /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento/{equipamentoProjetoId}         * Adiciona na tarefa um equipamento que já esteja associado ao projeto
 * POST     /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento-ids                            * Sincroniza na tarefa os equipamentos enviados como parâmetro ids. Ex.:  [1,3,4]  
 * DELETE   /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamento/{equipamentoProjetoId}      * Remove da tarefa um equipamento relacionado ao projeto
 * DELETE   /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamentos                            * Remove todos os equipamentos da tarefa
 */

Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-kit','API\TarefaController@addKitAll');
Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento/{equipamentoProjetoId}','API\TarefaController@addEquipamentoProjeto');
Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento-ids','API\TarefaController@syncEquipamentosProjeto');

Route::delete('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamento/{equipamentoProjetoId}','API\TarefaController@removeEquipamentoProjeto');
Route::delete('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamentos','API\TarefaController@clearEquipamentoProjeto');


/**
 * API      /api/checklist-cnmes
 */
Route::apiResource('checklist-cnmes', 'API\ChecklistCnmeController');
Route::get('checklist-cnmes/cc/status','API\ChecklistCnmeController@status');


/*
*
* Removidos - A proposta era criar um checkilist que fosse reaproveitado entre os projetos e contivesse informações mais detalhadas além dos requisitos dos
equipamentos


Route::apiResource('checklists', 'API\ChecklistController');
Route::post('checklists/{checklistId}/add-itemchecklist', 'API\ChecklistController@addItemChecklist');
Route::delete('checklists/{checklistId}/remove-itemchecklist/{itemId}', 'API\ChecklistController@removeItemChecklist');
*/


/*
* Removidos - Tratam itens do checklist de forma individual.
*
Route::post('checklist-cnmes/{checklistCnmeId}/clear-add-items-all', 'API\ChecklistCnmeController@clearAndAddItemsAll');
Route::post('checklist-cnmes/{checklistCnmeId}/add-itemchecklist', 'API\ChecklistCnmeController@addItemChecklist');
Route::delete('checklist-cnmes/{checklistCnmeId}/remove-itemchecklist/{itemId}', 'API\ChecklistCnmeController@removeItemChecklist');
*/



