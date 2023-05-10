<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateShowRepeatmetaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('show_repeatmeta', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            Schema::dropIfExists('show_repeatmeta');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        });

        Schema::create('show_repeatmeta', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('show_id')->unsigned();
            $table->date('repeat_start');
            $table->string('repeat_interval')->nullable()->default(null);
            $table->date('repeat_end')->nullable()->default(null);
            $table->string('repeat_year')->nullable()->default(null);
            $table->string('repeat_month')->nullable()->default(null);
            $table->string('repeat_day')->nullable()->default(null);
            $table->string('repeat_week')->nullable()->default(null);
            $table->string('repeat_weekday')->nullable()->default(null);

            $table->timestamps();

            $table->foreign('show_id')
                ->references('id')->on('shows')
                ->onDelete('cascade');

            $table->index('repeat_start');
            $table->index('repeat_end');
            $table->index('repeat_year');
            $table->index('repeat_month');
            $table->index('repeat_day');
            $table->index('repeat_week');
            $table->index('repeat_weekday');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('show_repeatmeta', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            Schema::dropIfExists('show_repeatmeta');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        });
    }

}
