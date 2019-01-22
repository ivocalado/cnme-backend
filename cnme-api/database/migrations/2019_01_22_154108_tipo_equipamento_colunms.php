<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TipoEquipamentoColunms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_equipamentos', function($table) {
            
            $table->string('nome', 255);
            $table->string('descricao', 255);
  
        });

        Schema::table('equipamentos', function($table) {
            $table->integer('tipo_equipamento_id')->unsigned()->nullable();
            $table->foreign('tipo_equipamento_id')->references('id')->on('tipo_equipamentos');
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
