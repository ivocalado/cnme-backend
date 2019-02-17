<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class KitColumns extends Migration
{
    
    public function up()
    {
        Schema::table('kits', function($table) {
            
            $table->string('nome', 255);
            $table->string('descricao', 255)->nullable();
            $table->string('versao', 100)->nullable();
            $table->string('status', 100);

            $table->date('data_inicio');
            $table->date('data_fim')->nullable();

            $table->integer('usuario_id')->unsigned()->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
        });

    }

    public function down()
    {
        
    }
}
