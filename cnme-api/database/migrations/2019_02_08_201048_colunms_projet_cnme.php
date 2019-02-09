<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ColunmsProjetCnme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projeto_cnmes', function (Blueprint $table) {
            $table->dropColumn('data_implantacao_prevista');
            $table->dropColumn('data_implantacao_realizada');
            $table->dropColumn('data_inicio_entrega');

            $table->date('data_finalizacao_prevista')->nullable();
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
