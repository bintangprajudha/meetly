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
        Schema::create('reposts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User yang melakukan repost
            $table->unsignedBigInteger('post_id'); // Post yang direpost
            $table->text('caption')->nullable(); // Caption tambahan untuk repost
            $table->json('images')->nullable(); // Gambar tambahan untuk repost
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');

            // Unique constraint agar user tidak bisa repost post yang sama lebih dari sekali
            // Dihapus agar user bisa repost berkali-kali
            // $table->unique(['user_id', 'post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reposts');
    }
};
