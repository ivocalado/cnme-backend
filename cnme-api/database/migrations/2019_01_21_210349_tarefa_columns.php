<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TarefaColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tarefas', function($table) {
          
            $table->string('nome', 255);
            $table->mediumText('descricao');
            $table->integer('numero');
            $table->string('status', 50);
            $table->string('tipo', 50);

            $table->date('data_inicio_prevista');
            $table->date('data_fim_prevista');

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();

            $table->integer('etapa_id')->unsigned();
            $table->foreign('etapa_id')->references('id')->on('etapas');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('users');

            $table->integer('responsavel_id')->unsigned()->nullable();
            $table->foreign('responsavel_id')->references('id')->on('users');

            $table->integer('unidade_responsavel_id')->unsigned()->nullable();
            $table->foreign('unidade_responsavel_id')->references('id')->on('unidades');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
