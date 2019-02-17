<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkLocalidadeUnidadeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unidades', function($table) {
            $table->integer('localidade_id')->unsigned()->nullable();
            $table->foreign('localidade_id')->references('id')->on('localidades')->onDelete('set null');


            $table->integer('tipo_unidade_id')->unsigned();
            $table->foreign('tipo_unidade_id')->references('id')->on('tipo_unidades');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unidades', function($table) {
            $table->dropForeign('localidade_id');
        });
    }
}
