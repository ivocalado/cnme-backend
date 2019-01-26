<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ItemChecklistColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itens_checklist', function($table) {

            $table->integer('checklist_id')->unsigned();
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');

            $table->string('descricao',255);
            $table->string('tipo',100);

           
            
            $table->integer('equipamento_id')->unsigned()->nullable();
            $table->foreign('equipamento_id')->references('id')->on('equipamentos');

        

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
