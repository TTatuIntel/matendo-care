<?php
// 2024_01_01_000005_create_doctor_patients_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->date('assigned_date')->default(now());
            $table->date('termination_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['doctor_id', 'patient_id', 'deleted_at']);
            $table->index(['doctor_id', 'status']);
            $table->index(['patient_id', 'status']);
            $table->index(['patient_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_patients');
    }
};