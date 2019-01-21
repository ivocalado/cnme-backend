<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSolicitacaoCnmesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitacao_cnmes', function (Blueprint $table) {
            $table->increments('id');

            $table->mediumText('descricao');
            $table->string('status',50);

            $table->integer('usuario_id')->unsigned()->nullable();
            $table->foreign('usuario_id')->references('id')->on('users');

            $table->integer('unidade_id')->unsigned();
            $table->foreign('unidade_id')->references('id')->on('unidades');

            $table->timestamp('data_solicitacao')->default(DB::raw('CURRENT_TIMESTAMP'));
            
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
        Schema::dropIfExists('solicitacao_cnmes');
    }
}
