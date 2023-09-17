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
        Schema::table('measurements', function (Blueprint $table) {
            $table->integer('length_svl_cm')->unsigned()->nullable()->after('grams');
            $table->integer('length_full_cm')->unsigned()->nullable()->after('grams');
            $table->integer('diameter_mid_body_cm')->unsigned()->nullable()->after('grams');
            $table->integer('scale_rows_mid_body_cm')->unsigned()->nullable()->after('grams');
            $table->integer('height_cm')->unsigned()->nullable()->after('grams');
            $table->boolean('birth_measurement')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            //
        });
    }
};
