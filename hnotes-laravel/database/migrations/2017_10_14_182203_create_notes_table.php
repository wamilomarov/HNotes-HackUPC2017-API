<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id')->unique();
            $table->tinyInteger('is_private')->default(1);
            $table->integer('user_id')->unisgned()->nullable();
//            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('image_url');
            $table->string('title');
            $table->mediumText('content');
            $table->timestamps();
        });

        Schema::create('notes_users_access', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unisgned()->nullable();
//            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('note_id')->unisgned()->nullable();
//            $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            $table->string('access_token');
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
        Schema::drop('notes');
    }
}
