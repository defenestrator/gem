<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->index(['status', 'availability', 'created_at'], 'idx_animals_status_avail_created');
        });

        Schema::table('classifieds', function (Blueprint $table) {
            $table->index(['status', 'price', 'created_at'], 'idx_classifieds_status_price_created');
        });

        // Partial index for approved media — more selective than the existing composite
        DB::statement("
            CREATE INDEX idx_media_approved_mediable
            ON media(mediable_type, mediable_id)
            WHERE moderation_status = 'approved'
        ");

        // Partial index for species lookups on published animals
        DB::statement("
            CREATE INDEX idx_animals_species_pub
            ON animals(species_id)
            WHERE status = 'published'
        ");

        Schema::table('species', function (Blueprint $table) {
            $table->index(['higher_taxa'], 'idx_species_higher_taxa');
        });

        // GIN trigram indexes for full-text fallback LIKE search
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX idx_animals_pet_name_gin ON animals USING gin(pet_name gin_trgm_ops)');
        DB::statement('CREATE INDEX idx_animals_description_gin ON animals USING gin(description gin_trgm_ops)');
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropIndex('idx_animals_status_avail_created');
        });

        Schema::table('classifieds', function (Blueprint $table) {
            $table->dropIndex('idx_classifieds_status_price_created');
        });

        DB::statement('DROP INDEX IF EXISTS idx_media_approved_mediable');
        DB::statement('DROP INDEX IF EXISTS idx_animals_species_pub');

        Schema::table('species', function (Blueprint $table) {
            $table->dropIndex('idx_species_higher_taxa');
        });

        DB::statement('DROP INDEX IF EXISTS idx_animals_pet_name_gin');
        DB::statement('DROP INDEX IF EXISTS idx_animals_description_gin');
    }
};
