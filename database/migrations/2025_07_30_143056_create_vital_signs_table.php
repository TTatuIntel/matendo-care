<?php

// 2024_01_01_000007_create_vital_signs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->integer('systolic')->nullable(); // mmHg
            $table->integer('diastolic')->nullable(); // mmHg
            $table->integer('heart_rate')->nullable(); // bpm
            $table->decimal('temperature', 4, 1)->nullable(); // Celsius
            $table->integer('respiratory_rate')->nullable(); // breaths per minute
            $table->integer('oxygen_saturation')->nullable(); // percentage
            $table->decimal('blood_sugar', 5, 1)->nullable(); // mg/dL
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('bmi', 4, 1)->nullable(); // calculated
            $table->integer('pain_level')->nullable(); // 0-10 scale
            $table->enum('mood', ['excellent', 'good', 'okay', 'poor', 'terrible'])->nullable();
            $table->boolean('is_critical')->default(false);
            $table->json('alerts')->nullable(); // Store alert messages
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'created_at']);
            $table->index(['patient_id', 'is_critical']);
            $table->index(['is_critical', 'created_at']);
            $table->index(['recorded_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};