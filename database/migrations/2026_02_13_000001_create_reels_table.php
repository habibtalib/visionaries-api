<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('content');
            $table->text('content_ms')->nullable();
            $table->string('author', 100)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('gradient', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reels'); }
};
