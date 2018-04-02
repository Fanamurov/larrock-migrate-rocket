<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMigrateDbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('migrate_db', function (Blueprint $table) {
            $table->char('old_id');
            $table->char('new_id');
            $table->char('table_name');
            $table->timestamps();

            $table->index(['old_id', 'new_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('migrate_db');
    }
}
