<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('email', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('to');
            $table->string('subject');
            $table->text('content');
            $table->string('status');
            $table->string('service')->nullable();
            $table->unsignedInteger('attempts')->default(0);
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
        Schema::dropIfExists('email');
    }
}
