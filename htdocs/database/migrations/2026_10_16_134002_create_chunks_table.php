<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chunks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->text('content');
            // Note 1: Vector column cannot be NULL (on MariaDB).
            // Note 2: Dimension is set to 768, because I use Ollama with embeddinggemma:latest.
            // $table->vector('embedding', dimensions: 768)->index(); // Note this will only set the default M (6) and DISTANCE (cosine) - see below.
            $table->vector('embedding', dimensions: 768);
            $table->unsignedInteger('token_count');
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents');
        });
        // Note that with Laravel v13.37.0 you can't still add a vector index the normal way.
        // M=48 will take more space, but gives more precision. The default value is 6.
        // See:
        // https://github.com/MariaDB/skills/blob/main/mariadb-vector/SKILL.md
        // M: Controls the HNSW graph connectivity (range 3–200). Higher values improve recall but cost more memory and slower inserts. Default is fine for most workloads; increase to ~16–32 for very large datasets where recall matters more than insert speed.
        DB::statement("ALTER TABLE chunks ADD VECTOR INDEX (`embedding`) M=32 DISTANCE=cosine");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chunks');
    }
};
