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
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'status')) {
                $table->enum('status', ['sent','delivered','read'])->default('sent');
            }

            if (!Schema::hasColumn('messages', 'images')) {
                $table->json('images')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('messages', 'images')) {
                $table->dropColumn('images');
            } 
        });
    }
};
