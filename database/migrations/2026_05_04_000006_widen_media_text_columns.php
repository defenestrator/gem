<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->text('title')->nullable()->change();
            $table->text('author')->nullable()->change();
            $table->text('copyright')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('author')->nullable()->change();
            $table->string('copyright')->nullable()->change();
        });
    }
};
