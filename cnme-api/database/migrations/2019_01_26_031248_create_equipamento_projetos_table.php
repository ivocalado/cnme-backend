<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipamentoProjetosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipamento_projetos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('equipamento_id')->unsigned();
            $table->foreign('equipamento_id')->references('id')->on('equipamentos');

            $table->integer('projeto_cnme_id')->unsigned();
            $table->foreign('projeto_cnme_id')->references('id')->on('projeto_cnmes')->onDelete('cascade');;

            $table->string('observacao')->nullable();
            $table->string('detalhes',255)->nullable();

            $table->string('status',20)->nullable();

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
        Schema::dropIfExists('equipamento_projetos');
    }
}
