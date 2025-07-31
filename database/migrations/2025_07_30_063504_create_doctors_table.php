<?php


// 2024_01_01_000003_create_doctors_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('specialization');
            $table->string('license_number')->unique();
            $table->string('hospital_affiliation')->nullable();
            $table->text('qualifications')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->time('consultation_start_time')->default('09:00');
            $table->time('consultation_end_time')->default('17:00');
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('accepts_emergency')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_available', 'accepts_emergency']);
            $table->index('specialization');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};