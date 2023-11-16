<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::create('appointment_dates', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('appointment_id');
			$table->date('date');
			$table->time('start_time');
			$table->time('end_time')->nullable();
			$table->timestamps();

			$table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
		});

	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        Schema::dropIfExists('appointment_dates');
		DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }
};
