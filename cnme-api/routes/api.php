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

Route::post('login', 'API\AuthController@login');
Route::post('usuarios/confirmar','API\UsuarioController@confirmar');
Route::get('usuarios/get', 'API\UsuarioController@getUserPorToken');

Route::get('novasenha/{email}', 'API\RecuperarSenhaController@solicitarNovaSenha');
Route::get('novasenha/validar/{email}/{token}','API\RecuperarSenhaController@validarSolicitacao');
Route::post('novasenha/usuario/{usuarioId}/atualizar', 'API\RecuperarSenhaController@atualizarSenha');



Route::middleware('jwt.auth')->group(function(){
    Route::get('logout', 'API\AuthController@logout');
    Route::get('refresh-token', 'API\AuthController@refresh');
    

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
     * GET    /api/unidades/u/polos/novos               * Polos sem projeto iniciado
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
    Route::get('unidades/u/mec','API\UnidadeController@mec');
    Route::get('unidades/u/admin','API\UnidadeController@admin');
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
     * API      /api/usuarios/u/status
     * API      /api/usuarios/u/nao-confirmados
     * GET      /api/usuarios/u/pesquisar                                   * Pesquisa por nome, cpf e email com o parâmetro q
     * GET      /api/usuarios/check-email-disponivel/{email}                * Verifica se o email está disponível
     * GET      /api/usuarios/check-cpf-disponivel/{cpf}                    * Verifica se o cpf está disponível
     * GET      /api/usuarios/u/all                                         * Todos, inclusive os removidos
     * GET      /api/usuarios/u/removidos                                   * Somente os removidos
     * DELETE   /api/usuarios/{id}/force-delete                             * Tenta remover de forma forçada
     * GET      /api/usuarios/{id}/restaurar                                * Restaurar
     */

    Route::apiResource('usuarios', 'API\UsuarioController');
    Route::post('usuarios/{id}/enviar-convite','API\UsuarioController@enviarConvite');
    Route::get('usuarios/u/tipos','API\UsuarioController@tipos');
    Route::get('usuarios/u/nao-confirmados','API\UsuarioController@getUsuariosNaoConfirmados');
    Route::get('usuarios/u/gestores-nao-confirmados','API\UsuarioController@getGestoresNaoConfirmados');
    Route::get('usuarios/u/pesquisar','API\UsuarioController@search');
    Route::get('usuarios/check-email-disponivel/{email}','API\UsuarioController@checkEmail');
    Route::get('usuarios/check-cpf-disponivel/{cpf}','API\UsuarioController@checkCpf');
    Route::get('usuarios/u/all','API\UsuarioController@all');
    Route::get('usuarios/u/removidos','API\UsuarioController@removidos');
    Route::delete('usuarios/{id}/force-delete','API\UsuarioController@forceDelete');
    Route::get('usuarios/{id}/restaurar','API\UsuarioController@restore');

    Route::post('usuarios/login/email','API\UsuarioController@findByEmail');


    /**################################################################################################################ */

    /** EQUIPAMENTOS E TIPOS
     * API      /api/tipoequipamentos
     * API      /api/equipamentos
     * GET      /api/equipamentos/e/pesquisar                    * Pesquisa por nome do equipamento com q, e/ou tipo do equipamento por tipo(string)
     * GET      /api/equipamentos/e/all                          * Todos, inclusive os removidos
     * GET      /api/equipamentos/e/removidos                    * Somente os removidos
     * DELETE   /api/equipamentos/{id}/force-delete              * Tenta remover de forma forçada
     */
    Route::apiResource('tipoequipamentos', 'API\TipoEquipamentoController');
    Route::apiResource('equipamentos', 'API\EquipamentoController');
    Route::get('equipamentos/e/pesquisar','API\EquipamentoController@search');
    Route::get('equipamentos/e/all','API\EquipamentoController@all');
    Route::get('equipamentos/e/removidos','API\EquipamentoController@removidos');
    Route::delete('equipamentos/{id}/force-delete','API\EquipamentoController@forceDelete');
    Route::get('equipamentos/{id}/restaurar','API\EquipamentoController@restore');

    /**
     *
     * ######################################################################################
     *  
     * API      /api/kits
     * GET      kits/{kitId}/diffKit/equipamentos                           *
     * POST     /api/kits/{kitId}/add-equipamento/{equipamentoId}           * Adiciona um equimamento específico ao kit
     * POST     /api/kits/{kitId}/add-equipamentos                          * Adiciona uma lista de equimamentos enviada no body no campo ids. Ex.: [2,3,4,5,6,7]
     * DELETE   /api/kits/{kitId}/remove-equipamento/{equipamentoId}        * Remove um equipamento específico
     * DELETE   /api/kits/{kitId}/remove-equipamentos                       * Remove uma lista de equipamentos enviada no body no campo ids. Ex.: [2,3,4,5,6,7]
     * GET      /api/kits/k/all                                             * Todos, inclusive os removidos
     * GET      /api/kits/k/removidos                                       * Somente os removidos
     * DELETE   /api/kits/{id}/force-delete                                 * Tenta remover de forma forçada
     * GET      /api/kits/{id}/restaurar                                       * Restaurar equipamentos
     */
    Route::apiResource('kits', 'API\KitController');
    Route::get('kits/{kitId}/diffKit/equipamentos','API\KitController@diffKit');
    Route::post('kits/{kitId}/add-equipamento/{equipamentoId}', 'API\KitController@addEquipamento');
    Route::post('kits/{kitId}/add-equipamentos', 'API\KitController@addEquipamentoList');
    Route::delete('kits/{kitId}/remove-equipamento/{equipamentoId}', 'API\KitController@removeEquipamento');
    Route::delete('kits/{kitId}/remove-equipamentos', 'API\KitController@removeEquipamentoList');
    Route::get('kits/k/all','API\KitController@all');
    Route::get('kits/k/removidos','API\KitController@removidos');
    Route::delete('kits/{id}/force-delete','API\KitController@forceDelete');
    Route::get('kits/{id}/restaurar','API\KitController@restore');
    

        /**
     * 
     * ##########################################################################################
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
     *                                                                                                          ATIVADO,CANCELADO)
                                                                                                    
    *  GET              /api/projeto-cnme/p/atrasados                                               * Pesquisar por atrasados
    */

    Route::apiResource('projeto-cnme', 'API\ProjetoController');
    Route::get('projeto-cnme/{id}/validar', 'API\ProjetoController@validar');
    Route::get('projeto-cnme/{id}/etapas','API\ProjetoController@etapas');
    Route::get('projeto-cnme/{id}/tarefas','API\ProjetoController@tarefas');
    Route::get('projeto-cnme/p/status','API\ProjetoController@status');

    Route::post('projeto-cnme/criar','API\ProjetoController@store');
    Route::post('projeto-cnme/{projetoId}/cancelar','API\ProjetoController@cancelar');
    Route::post('projeto-cnme/{projetoId}/recuperar','API\ProjetoController@recuperar');

    Route::post('projeto-cnme/{projetoId}/add-equipamento/{equipamentoId}','API\PlanejamentoController@addEquipamento');
    Route::post('projeto-cnme/{projetoId}/add-equipamentos','API\PlanejamentoController@addEquipamentoList');
    Route::delete('projeto-cnme/{projetoId}/remove-equipamentos','API\PlanejamentoController@removeEquipamentoList');
    Route::delete('projeto-cnme/{projetoId}/remove-equipamento/{equipamentoProjetoId}','API\PlanejamentoController@removeEquipamento');
    Route::post('projeto-cnme/{projetoId}/add-kit/{kitId}','API\PlanejamentoController@addKit');
    Route::delete('projeto-cnme/{projetoId}/remove-kit/{kitId}','API\PlanejamentoController@removeKit');

    Route::get('projeto-cnme/{projetoId}/equipamentos/status/{status}', 'API\ProjetoController@equipamentosPorStatus');
    Route::get('projeto-cnme/p/pesquisar', 'API\ProjetoController@search');
    Route::get('projeto-cnme/p/atrasados', 'API\ProjetoController@atrasados');
    Route::get('projeto-cnme/{projetoId}/etapa-envio','API\ProjetoController@getEtapaEnvio');
    Route::get('projeto-cnme/{projetoId}/etapa-instalacao','API\ProjetoController@getEtapaInstalacao');
    Route::get('projeto-cnme/{projetoId}/etapa-ativacao','API\ProjetoController@getEtapaAtivacao');
    Route::get('projeto-cnme/{projetoId}/etapas/{tipo}','API\ProjetoController@getEtapasPorTipo');

    Route::get('projeto-cnme/empresa/{responsavelId}/projetos','API\ProjetoController@projetosPorResponsavel');

    
    /**
     * ##################################################################################
     */
    Route::apiResource('etapas', 'API\EtapaController');

    Route::get('etapas/e/status','API\EtapaController@status');
    Route::get('etapas/e/tipos','API\EtapaController@tipos');

    Route::post('etapas/projeto-cnme/{projetoId}/add-tarefa-envio','API\EnviarController@addTarefaEnvio');
    Route::post('etapas/projeto-cnme/{projetoId}/tarefa/{tarefaId}/enviar','API\EnviarController@enviar');
    
    Route::post('etapas/projeto-cnme/{projetoId}/enviar-all','API\EnviarController@enviarAll');
    Route::post('etapas/projeto-cnme/{projetoId}/tarefa/{tarefaId}/entregar','API\EnviarController@entregar');

    //Route::delete('etapas/{etapaId}/remove-tarefa/{tarefaId}','API\EtapaController@removeTarefa');
    Route::put('etapas/{etapaId}/update-tarefa/{tarefaId}','API\EtapaController@updateTarefa');

    Route::get('etapas/{etapaId}/equipamentos','API\EtapaController@equipamentos');

    Route::post('etapas/projeto-cnme/{projetoId}/add-tarefa-instalacao','API\InstalacaoController@addTarefaInstalacao');
    Route::post('etapas/projeto-cnme/{projetoId}/add-tarefa-ativacao','API\AtivacaoController@addTarefAtivacao');

    Route::put('etapas/projeto-cnme/{projetoId}/update-tarefa-instalacao','API\InstalacaoController@updateTarefaInstalacao');
    Route::put('etapas/projeto-cnme/{projetoId}/update-tarefa-ativacao','API\AtivacaoController@updateTarefaAtivacao');

    Route::post('etapas/projeto-cnme/{projetoId}/instalar','API\InstalacaoController@instalar');
    Route::post('etapas/projeto-cnme/{projetoId}/ativar','API\AtivacaoController@ativar');

    Route::get('etapas/projeto-cnme/{projetoId}/tarefa/{tarefaId}/enviar/notificar','API\EnviarController@notificar');
    Route::get('etapas/projeto-cnme/{projetoId}/enviar-all/notificar','API\EnviarController@notificar');
    Route::get('etapas/projeto-cnme/{projetoId}/tarefa/{tarefaId}/entregar/notificar','API\EnviarController@notificar');
    Route::get('etapas/projeto-cnme/{projetoId}/instalar/notificar','API\InstalacaoController@notificar');
    Route::get('etapas/projeto-cnme/{projetoId}/ativar/notificar','API\AtivacaoController@notificar');

    /**
     * POST     /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamentos-all                                        * Adiciona na tarefa todos os equipamentos do projeto naquela tarefa
     * POST     /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento/{equipamentoProjetoId}         * Adiciona na tarefa um equipamento que já esteja associado ao projeto
     * POST     /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento-ids                            * Sincroniza na tarefa os equipamentos enviados como parâmetro ids. Ex.:  [1,3,4]  
     * DELETE   /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamento/{equipamentoProjetoId}      * Remove da tarefa um equipamento relacionado ao projeto
     * DELETE   /api/tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamentos                            * Remove todos os equipamentos da tarefa
     */

    Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamentos-all','API\TarefaController@addEquipamentosAll');
    Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento/{equipamentoProjetoId}','API\TarefaController@addEquipamentoProjeto');
    Route::post('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/add-equipamento-ids','API\TarefaController@syncEquipamentosProjeto');
    Route::delete('tarefas/{id}','API\TarefaController@destroy');

    Route::delete('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamento/{equipamentoProjetoId}','API\TarefaController@removeEquipamentoProjeto');
    Route::delete('tarefas/projeto-cnme/{projetoId}/tarefas/{tarefaId}/remove-equipamentos','API\TarefaController@clearEquipamentoProjeto');

    Route::get('tarefas/projeto-cnme/{projetoId}/equipamentos-disponiveis-envio','API\TarefaController@equipamentosDisponiveisEnvio');
    
    Route::get('tarefas/empresas/{empresaId}/tarefas','API\TarefaController@tarefasPorResponsavel');

    Route::get('tarefas/t/status','API\TarefaController@status');
    
    /**
    * ###################################################################################################### 
    * API      /api/checklist-cnmes
     */
    Route::apiResource('checklist-cnmes', 'API\ChecklistCnmeController');
    Route::get('checklist-cnmes/cc/status','API\ChecklistCnmeController@status');




    /**
     * #############################################################################
     * Dashboards
     * 
     */

    Route::get('dashboard/projetos/total','API\DashboardController@countProjetos');
    Route::get('dashboard/projetos/status','API\DashboardController@queryPorStatus');
    Route::get('dashboard/projetos/estados','API\DashboardController@queryPorEstado');
    Route::get('dashboard/projetos/estados/all','API\DashboardController@queryPorEstadoAll');
    Route::get('dashboard/projetos/atrasados/total','API\DashboardController@countAtrasados');
    Route::get('dashboard/projetos/extrato','API\DashboardController@queryExtrato');
    Route::get('dashboard/projetos/etapas/extrato','API\DashboardController@queryProjetosEtapasExtrato');
    Route::get('dashboard/projetos/{etapa}/atrasados/total','API\DashboardController@countAtrasadosPorEtapa');
    Route::get('dashboard/usuarios/gestores/nao-confirmados/total','API\DashboardController@countGestoresNaoConfirmados');
    Route::get('dashboard/projetos/estado/status','API\DashboardController@queryStatusEstados');
    Route::get('dashboard/projetos/{etapa}/empresas','API\DashboardController@queryPrestadoras');
    Route::get('dashboard/projetos/{etapa}/empresas/{empresaId}/estados','API\DashboardController@queryPrestadoraPorEstado');

    Route::get('dashboard/projetos/{etapa}/prazos/estados','API\DashboardController@querPrazosTarefasPorEstado');
    Route::get('dashboard/projetos/{etapa}/prazos/{uf}/municipios','API\DashboardController@querPrazosTarefasPorMunicipio');

    Route::get('dashboard/projetos/meses','API\DashboardController@queryProjeto12Meses');
    Route::get('dashboard/projetos/estados/meses','API\DashboardController@queryProjetoEstadoAno');
});








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



