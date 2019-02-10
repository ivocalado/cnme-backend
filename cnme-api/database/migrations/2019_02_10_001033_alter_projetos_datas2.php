<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjetosDatas2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projeto_cnmes', function (Blueprint $table) {
            if (Schema::hasColumn('projeto_cnmes', 'data_finalizacao_prevista')){
                $table->dropColumn('data_finalizacao_prevista');
            }
           

            $table->date('data_inicio_previsto')->nullable();
            $table->date('data_fim_previsto')->nullable();

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
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
