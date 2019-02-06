<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecklistCnmesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklist_cnmes', function (Blueprint $table) {
            $table->increments('id');

            // $table->integer('checklist_id')->unsigned();
            // $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');

            $table->integer('projeto_cnme_id')->unsigned();
            $table->foreign('projeto_cnme_id')->references('id')->on('projeto_cnmes')->onDelete('cascade');

            $table->string('descricao', 255);

            $table->string('status', 20);

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
        Schema::dropIfExists('checklist_cnmes');
    }
}
