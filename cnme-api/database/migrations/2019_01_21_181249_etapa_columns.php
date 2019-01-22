<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EtapaColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('etapas', function($table) {
            
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');

            $table->string('descricao', 255);

            $table->string('status', 50);
            $table->string('tipo',50);

            $table->date('data_inicio_prevista');
            $table->date('data_fim_prevista');

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();

            $table->integer('projeto_cnme_id')->unsigned();
            $table->foreign('projeto_cnme_id')->references('id')->on('projeto_cnmes');

            $table->integer('usuario_id')->unsigned()->nullable();
            $table->foreign('usuario_id')->references('id')->on('users');

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
        //
    }
}
