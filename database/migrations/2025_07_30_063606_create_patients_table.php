<?php
// 2024_01_01_000004_create_patients_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('patient_id')->unique();
            $table->string('insurance_number')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->string('primary_physician')->nullable();
            $table->decimal('height', 5, 2)->nullable(); // in cm
            $table->decimal('weight', 5, 2)->nullable(); // in kg
            $table->date('last_checkup_date')->nullable();
            $table->date('next_appointment_date')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('risk_level');
            $table->index(['risk_level', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
