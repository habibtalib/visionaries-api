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
        Schema::table('community_posts', function (Blueprint $table) {
            $table->string('prompt_badge', 50)->nullable()->after('content');
            $table->integer('likes_count')->default(0)->after('prompt_badge');
            $table->integer('comments_count')->default(0)->after('likes_count');
            $table->integer('shares_count')->default(0)->after('comments_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn(['prompt_badge', 'likes_count', 'comments_count', 'shares_count']);
        });
    }
};