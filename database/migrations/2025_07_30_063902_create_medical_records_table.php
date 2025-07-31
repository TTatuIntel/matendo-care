<?php

// 2024_01_01_000006_create_medical_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->enum('category', [
                'vitals', 'labs', 'imaging', 'consultation', 'diagnosis', 
                'treatment', 'medication', 'allergy', 'immunization',
                'surgery', 'emergency', 'discharge', 'follow_up', 'other'
            ]);
            $table->json('data');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->boolean('is_critical')->default(false);
            $table->boolean('requires_attention')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'category']);
            $table->index(['patient_id', 'is_critical']);
            $table->index(['requires_attention', 'reviewed_at']);
            $table->index(['recorded_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};