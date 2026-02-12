<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('traits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->text('why_template')->nullable();
            $table->text('daily_template')->nullable();
            $table->text('opposite_template')->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('is_default')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('traits'); }
};
