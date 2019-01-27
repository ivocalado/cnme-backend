<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemChecklistCnmesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_checklist_cnmes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('checklist_cnme_id')->unsigned();
            $table->foreign('checklist_cnme_id')->references('id')->on('checklist_cnmes')->onDelete('cascade');

            $table->integer('item_checklist_id')->unsigned()->nullable();
            $table->foreign('item_checklist_id')->references('id')->on('itens_checklist')->onDelete('cascade');

            $table->string('status', 20);

            $table->mediumText('descricao')->nullable();
            $table->mediumText('observacao')->nullable();

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
        Schema::dropIfExists('item_checklist_cnmes');
    }
}
