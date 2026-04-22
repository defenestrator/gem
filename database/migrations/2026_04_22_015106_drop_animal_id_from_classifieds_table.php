<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('classifieds', function (Blueprint $table) {
            $table->dropForeign(['animal_id']);
            $table->dropIndex(['animal_id']);
            $table->dropColumn('animal_id');
        });
    }

    public function down(): void
    {
        Schema::table('classifieds', function (Blueprint $table) {
            $table->unsignedBigInteger('animal_id')->nullable();
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('set null');
            $table->index('animal_id');
        });
    }
};
