<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBetSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bet_selections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bet_id');
            $table->unsignedBigInteger('selection_id');
            $table->double('odds', 7, 3);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bet_selections');
    }
}
