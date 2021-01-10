<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->id();
            $table->string('room_code',50);
            $table->string('room_type',50);
            $table->string('bed_type',50);
            $table->tinyInteger('bed_count')->default(0);
            $table->double('room_price',10,2);
            $table->tinyInteger('guest_capacity');
            $table->string('room_picture')->nullable();
            $table->foreignId('hotel_id')->constrained('hotel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room');
    }
}
