<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                  ->constrained('email_conversations')
                  ->cascadeOnDelete();
            $table->string('direction'); // inbound | outbound
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->text('body_text')->nullable();
            $table->text('body_html')->nullable();
            $table->string('sendgrid_message_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_messages');
    }
};
