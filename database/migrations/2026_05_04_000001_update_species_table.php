<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropColumn('type_species');
        });

        Schema::table('species', function (Blueprint $table) {
            $table->string('type_species', 10)->nullable()->after('id');
            $table->unique('species_number');
        });
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropUnique(['species_number']);
            $table->dropColumn('type_species');
        });

        Schema::table('species', function (Blueprint $table) {
            $table->boolean('type_species')->default(false)->after('id');
        });
    }
};
