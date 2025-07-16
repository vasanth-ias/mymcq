<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to users table
            $table->integer('score')->default(0); // User's score for this attempt
            $table->integer('total_questions')->default(0); // Total questions in this attempt
            $table->json('attempt_details')->nullable(); // Store details like {question_id: {selected_option: "A", is_correct: true}, ...}
            $table->timestamp('started_at')->nullable(); // When the quiz started
            $table->timestamp('completed_at')->nullable(); // When the quiz completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};