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
		Schema::create('appointments', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('location')->nullable();
			$table->text('description')->nullable();
			$table->date('expiration_date');
			$table->timestamps();
		});

	}

	/**
	 * Reverse the migrations.
	 */
	public function down() {
		DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
		Schema::dropIfExists('appointments');
		DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
	}

};
