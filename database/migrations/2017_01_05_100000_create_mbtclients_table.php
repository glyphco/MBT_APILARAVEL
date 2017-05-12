<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMbtclientsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		Schema::dropIfExists('mbtclients');
		DB::statement('SET FOREIGN_KEY_CHECKS = 1');
		Schema::create('mbtclients', function (Blueprint $table) {
			$table->string('id', 40)->primary();
			$table->string('secret', 40);
			$table->string('name');
			$table->timestamps();

			$table->unique(['id', 'secret']);

		});

		DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		$datetime = \Carbon\Carbon::now();

		$clients = [
			[
				'id'         => 'PostmanAccess',
				'secret'     => 'kB2kJM!yjF6brr7>d"Fp6l3S9*BMBT',
				'name'       => 'Postman Client Access',
				'created_at' => $datetime,
				'updated_at' => $datetime,
			],
			[
				'id'         => 'MBTWEB1',
				'secret'     => 'bdcqaE3aCmtfpgNVrecE7LWQT8AMBT',
				'name'       => 'MBT Web Access',
				'created_at' => $datetime,
				'updated_at' => $datetime,
			],
			[
				'id'         => 'MBTIOS1',
				'secret'     => 'KcrmCB4p0xHRWcq12GpyJjxbEayMBT',
				'name'       => 'MBT IOS Client',
				'created_at' => $datetime,
				'updated_at' => $datetime,
			],
		];

		DB::table('mbtclients')->truncate();
		DB::table('mbtclients')->insert($clients);

		DB::statement('SET FOREIGN_KEY_CHECKS = 1');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		Schema::dropIfExists('mbtclients');
		DB::statement('SET FOREIGN_KEY_CHECKS = 1');
	}
}
