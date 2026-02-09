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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Duration in minutes
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('type')->default('mcq'); // mcq, text
            $table->text('explanation')->nullable(); // For feedback
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'explanation']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'duration_minutes']);
        });
    }
};
