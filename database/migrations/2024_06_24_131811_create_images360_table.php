<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image360s', function (Blueprint $table) {
            $table->id();
            $table->text('image360');
            $table->unsignedBigInteger('product_id');
            $table->text('model')->nullable();
            $table->unsignedBigInteger('object_id')->default(0);
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
        Schema::dropIfExists('images360');
    }
};
