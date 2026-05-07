<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species_content_submissions', function (Blueprint $table) {
            $table->id();
            $table->morphs('submittable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('proposed_value');
            $table->string('status', 10)->default('pending'); // pending | approved | rejected
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['submittable_type', 'submittable_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_content_submissions');
    }
};
