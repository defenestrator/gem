<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->jsonb('description_revisions')->default('[]')->after('description');
        });

        Schema::table('subspecies', function (Blueprint $table) {
            $table->jsonb('description_revisions')->default('[]')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropColumn('description_revisions');
        });

        Schema::table('subspecies', function (Blueprint $table) {
            $table->dropColumn('description_revisions');
        });
    }
};
