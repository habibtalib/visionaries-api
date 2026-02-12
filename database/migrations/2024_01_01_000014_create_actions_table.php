<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('domain', 30);
            $table->string('frequency', 20);
            $table->text('alignment');
            $table->time('scheduled_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'domain']);
            $table->index(['user_id', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('actions'); }
};
