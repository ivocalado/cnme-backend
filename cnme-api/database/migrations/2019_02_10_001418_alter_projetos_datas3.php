<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjetosDatas3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projeto_cnmes', function($table)
        {
            if (Schema::hasColumn('projeto_cnmes', 'data_finalizacao_prevista')){
                $table->dropColumn('data_finalizacao_prevista');
            }
            
            $table->string('data_inicio_previsto', 255)->nullable(false)->change();
            $table->string('data_fim_previsto', 255)->nullable(false)->change();
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
