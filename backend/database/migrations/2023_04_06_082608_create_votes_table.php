<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('votes', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('appointment_date_id');
			$table->string('user_name');
			$table->timestamps();

			$table->foreign('appointment_date_id')->references('id')->on('appointment_dates')->onDelete('cascade');
		});

	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('votes');
	}
};
