<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class KitEquipamentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit_equipamento', function (Blueprint $table) {
            $table->increments('id');


            $table->integer('kit_id')->unsigned();
            $table->integer('equipamento_id')->unsigned();        
            
            $table->foreign('kit_id')
                        ->references('id')
                        ->on('kits')
                        ->onDelete('cascade');
                        
            $table->foreign('equipamento_id')
                        ->references('id')
                        ->on('equipamentos')
                        ->onDelete('cascade');


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
        Schema::dropIfExists('kit_equipamento');
    }
}
