<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClasseUnidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unidades', function($table) {

            $table->string('classe',20)->default('polo');
            $table->mediumText('descricao')->nullable();
            
            $table->string('email_institucional')->nullable();
            $table->boolean('admin')->default(false);

        });

        Schema::table('tipo_unidades', function($table) {

            $table->string('classe',20)->default('polo');

            $table->boolean('admin')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropColumn('classe');
        });

        Schema::table('tipo_unidades', function (Blueprint $table) {
            $table->dropColumn('classe');
        });
    }
}
