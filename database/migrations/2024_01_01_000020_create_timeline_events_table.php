<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 30);
            $table->string('category', 20);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'category']);
        });
    }
    public function down(): void { Schema::dropIfExists('timeline_events'); }
};
