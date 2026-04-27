<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->text('body');
            $table->timestamps();

            $table->index('inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_replies');
    }
};
