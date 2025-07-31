<?php

// 2024_01_01_000013_create_health_goals_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'weight_loss', 'weight_gain', 'exercise', 'diet', 'blood_pressure',
                'blood_sugar', 'cholesterol', 'heart_rate', 'sleep', 'stress',
                'medication_adherence', 'smoking_cessation', 'other'
            ]);
            $table->decimal('target_value', 10, 2);
            $table->decimal('current_value', 10, 2)->default(0);
            $table->string('unit'); // kg, steps, hours, mg/dl, etc.
            $table->date('start_date')->default(now());
            $table->date('target_date');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->integer('progress')->default(0); // percentage
            $table->boolean('is_achieved')->default(false);
            $table->date('achieved_date')->nullable();
            $table->json('milestones')->nullable(); // Array of milestone data
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
            $table->index(['patient_id', 'category']);
            $table->index(['status', 'target_date']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_goals');
    }
};