<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupmembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groupmembers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->string('group_name')->nullable();
            $table->string('position')->nullable();
            $table->integer('member_id')->unsigned()->nullable();
            $table->string('member_name')->nullable();

            $table->dateTime('start')->nullable()->default(null);
            $table->dateTime('end')->nullable()->default(null);

            $table->boolean('public')->default(0);
            $table->boolean('confirmed')->default(0);

            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable()->default(null);
            $table->unsignedInteger('updated_by')->nullable()->default(null);

            $table->foreign('group_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('cascade');

        });

        DB::statement('ALTER TABLE groupmembers ADD CONSTRAINT chk_null check (group_id is not null or member_id is not null);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groupmembers');
    }

}
