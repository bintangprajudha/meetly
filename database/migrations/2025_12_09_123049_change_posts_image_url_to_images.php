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
        Schema::table('posts', function (Blueprint $table) {
            // Cek dulu apakah kolom ada sebelum drop
            if (Schema::hasColumn('posts', 'image_url')) {
                $table->dropColumn('image_url');
            }

            // Cek dulu apakah kolom sudah ada sebelum tambah
            if (!Schema::hasColumn('posts', 'images')) {
                $table->json('images')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'images')) {
                $table->dropColumn('images');
            }

            if (!Schema::hasColumn('posts', 'image_url')) {
                $table->string('image_url')->nullable();
            }
        });
    }
};
