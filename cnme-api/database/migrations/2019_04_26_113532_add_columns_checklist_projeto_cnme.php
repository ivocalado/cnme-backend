<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsChecklistProjetoCnme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projeto_cnmes', function (Blueprint $table) {
            $table->dateTime('checklist_at')->nullable();
            $table->integer('checklist_id')->unsigned()->nullable();
            $table->foreign('checklist_id')->references('id')->on('checklists');

            $table->integer('usuario_checklist_id')->unsigned()->nullable();
            $table->foreign('usuario_checklist_id')->references('id')->on('users');

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
