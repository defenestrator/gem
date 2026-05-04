<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subspecies')) {
            return;
        }

        Schema::create('subspecies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->constrained('species')->nullOnDelete();
            $table->string('genus');
            $table->string('species');
            $table->string('subspecies');
            $table->string('author')->nullable();
            $table->timestamps();

            $table->index('species_id');
            $table->index(['genus', 'species', 'subspecies']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subspecies');
    }
};
