<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20);
            $table->jsonb('answers');
            $table->text('reflection_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('quiz_attempts'); }
};
