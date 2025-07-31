<?php
// 2024_01_01_000010_create_notifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', [
                'critical_vital_signs', 'appointment_reminder', 'medication_reminder',
                'document_verification', 'system_alert', 'emergency_alert',
                'lab_results', 'prescription_ready', 'follow_up_required',
                'insurance_update', 'system_maintenance', 'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_actionable')->default(false);
            $table->string('action_taken')->nullable();
            $table->timestamp('action_taken_at')->nullable();
            $table->foreignId('action_taken_by')->nullable()->constrained('users');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'priority']);
            $table->index(['type', 'created_at']);
            $table->index(['priority', 'is_read']);
            $table->index(['is_actionable', 'action_taken']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};