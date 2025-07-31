<?php
// 2024_01_01_000011_create_comments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // This already creates the index automatically
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->enum('type', [
                'general', 'medical_note', 'observation', 'diagnosis',
                'treatment', 'follow_up', 'prescription', 'lab_note',
                'progress_note', 'discharge_note', 'other'
            ])->default('general');
            $table->boolean('is_private')->default(false);
            $table->boolean('is_important')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'created_at']);
            $table->index(['patient_id', 'type']);
            $table->index(['is_important', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};