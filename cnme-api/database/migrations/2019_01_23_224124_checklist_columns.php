<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChecklistColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklists', function($table) {
            $table->string('versao', 100);

            $table->string('descricao', 255);

            $table->string('status', 20);

            $table->boolean('ativo')->default(true);

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('users'); 

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
