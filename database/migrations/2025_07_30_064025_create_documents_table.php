<?php

// 2024_01_01_000008_create_documents_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path')->nullable();
            $table->string('mime_type');
            $table->bigInteger('size'); // in bytes
            $table->enum('category', [
                'lab_results', 'medical_reports', 'prescriptions', 'imaging',
                'insurance', 'discharge_summary', 'consultation_notes',
                'referral_letters', 'test_results', 'vaccination_records', 'other'
            ]);
            $table->text('description')->nullable();
            $table->longText('file_content')->nullable(); // Base64 encoded file content
            $table->string('uploader_name')->nullable();
            $table->string('uploader_hospital')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('documents');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'category']);
            $table->index(['patient_id', 'is_verified']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
