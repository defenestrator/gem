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
        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('classified_id')->nullable()->after('animal_id')->nullOnDelete()->constrained();
            $table->index('classified_id');

            // make animal_id nullable so inquiries can belong to either
            $table->foreignId('animal_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropForeign(['classified_id']);
            $table->dropIndex(['classified_id']);
            $table->dropColumn('classified_id');
            $table->foreignId('animal_id')->nullable(false)->change();
        });
    }
};
