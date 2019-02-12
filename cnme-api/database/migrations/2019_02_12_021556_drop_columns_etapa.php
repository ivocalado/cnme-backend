<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsEtapa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('etapas', function($table){
          
            $table->dropColumn('data_inicio');
            $table->dropColumn('data_fim');
            $table->dropColumn('data_inicio_prevista');
            $table->dropColumn('data_fim_prevista');
            
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
