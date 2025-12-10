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
        Schema::table('reposts', function (Blueprint $table) {
            // Add images column if it doesn't exist
            if (!Schema::hasColumn('reposts', 'images')) {
                $table->json('images')->nullable()->after('caption');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reposts', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
