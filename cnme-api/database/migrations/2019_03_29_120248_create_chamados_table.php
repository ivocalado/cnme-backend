<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChamadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('tipo_chamados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome',100);
            $table->string('descricao', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('status_chamados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome',100);
            $table->string('descricao', 255)->nullable();
            $table->timestamps();
        });


        Schema::create('chamados', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('assunto',255);
            $table->longText('descricao');

            $table->integer('projeto_cnme_id')->unsigned();
            $table->foreign('projeto_cnme_id')->references('id')->on('projeto_cnmes')->onDelete('cascade');

            $table->integer('tarefa_id')->unsigned()->nullable();
            $table->foreign('tarefa_id')->references('id')->on('tarefas')->onDelete('set null');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('users');

            $table->integer('usuario_responsavel_id')->unsigned()->nullable();
            $table->foreign('usuario_responsavel_id')->references('id')->on('users');

            $table->integer('unidade_responsavel_id')->unsigned();
            $table->foreign('unidade_responsavel_id')->references('id')->on('unidades');

            $table->dateTime('data_inicio')->nullable();
            $table->dateTime('data_fim')->nullable(); 

            $table->integer('status_id')->unsigned()->default(1);
            $table->foreign('status_id')->references('id')->on('status_chamados');
            //1 Novo
            //2 Aberto
            //3 Rejeitado
            //4 Em andamento
            //5 Resolvido

            $table->integer('tipo_id')->unsigned()->default(1);
            $table->foreign('tipo_id')->references('id')->on('tipo_chamados');


            $table->integer('prioridade')->default(2); 
            //1 Baixa
            //2 Normal
            //3 Alta
            //4 Urgente
            //5 Imediata

            $table->boolean('privado')->default('false');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chamados');
    }
}
