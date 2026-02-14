<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('post_id');
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('post_id')->references('id')->on('community_posts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_comments');
    }
};