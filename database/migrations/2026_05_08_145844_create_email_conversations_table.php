<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('contact_email')->index();
            $table->string('contact_name')->nullable();
            $table->string('subject');
            $table->string('status')->default('open'); // open | closed | spam
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_conversations');
    }
};
