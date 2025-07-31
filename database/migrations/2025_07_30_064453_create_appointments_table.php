<?php
// 2024_01_01_000009_create_appointments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', [
                'scheduled', 'confirmed', 'in_progress', 'completed', 
                'cancelled', 'no_show', 'rescheduled'
            ])->default('scheduled');
            $table->enum('type', [
                'consultation', 'follow_up', 'check_up', 'emergency',
                'procedure', 'therapy', 'vaccination', 'screening', 'other'
            ])->default('consultation');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('pre_appointment_notes')->nullable();
            $table->text('post_appointment_notes')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('meeting_link')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['doctor_id', 'appointment_date']);
            $table->index(['patient_id', 'appointment_date']);
            $table->index(['appointment_date', 'status']);
            $table->index(['status', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};