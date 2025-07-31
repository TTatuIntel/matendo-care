<?php
// 2024_01_01_000012_create_medications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('dosage');
            $table->string('frequency');
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly', 'as_needed'])->default('daily');
            $table->text('instructions')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->foreignId('prescribed_by')->constrained('doctors');
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['prescribed', 'dispensed', 'completed', 'discontinued'])->default('prescribed');
            $table->time('reminder_times')->nullable(); // JSON array of times
            $table->timestamp('last_taken_at')->nullable();
            $table->timestamp('next_dose_at')->nullable();
            $table->integer('adherence_score')->default(100); // percentage
            $table->json('side_effects')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'is_active']);
            $table->index(['patient_id', 'status']);
            $table->index(['prescribed_by', 'created_at']);
            $table->index(['is_active', 'next_dose_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};