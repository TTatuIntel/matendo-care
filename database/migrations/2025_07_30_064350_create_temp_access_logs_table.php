<?php
// 2024_01_01_000014_create_temp_access_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temp_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->string('external_doctor_name');
            $table->string('external_doctor_email');
            $table->string('hospital_name')->nullable();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamp('first_accessed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('access_count')->default(0);
            $table->json('access_log')->nullable(); // IP addresses, user agents, etc.
            $table->text('notes')->nullable();
            $table->text('access_restrictions')->nullable(); // What can be viewed
            $table->timestamps();
            $table->softDeletes();

            $table->index(['token', 'is_active']);
            $table->index(['patient_id', 'doctor_id']);
            $table->index(['expires_at', 'is_active']);
            $table->index(['external_doctor_email', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temp_access_logs');
    }
};