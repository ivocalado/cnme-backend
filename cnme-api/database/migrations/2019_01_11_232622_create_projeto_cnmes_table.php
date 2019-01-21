<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjetoCnmesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projeto_cnmes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero', 50);
            $table->string('status', 50);

            $table->mediumText('descricao');
            $table->integer('unidade_id')->unsigned();
            $table->foreign('unidade_id')->references('id')->on('unidades');

            $table->integer('usuario_id')->unsigned()->nullable();
            $table->foreign('usuario_id')->references('id')->on('users');

            $table->integer('solicitacao_cnme_id')->unsigned();
            $table->foreign('solicitacao_cnme_id')->references('id')->on('solicitacao_cnmes');

            $table->date('data_criacao')->nullable();
            $table->date('data_implantacao_prevista')->nullable();
            $table->date('data_implantacao_realizada')->nullable();

            $table->date('data_inicio_entrega')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projeto_cnmes');
    }
}
