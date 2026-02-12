<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('review_type', 10);
            $table->date('period_start');
            $table->date('period_end');
            $table->text('vision_reflection')->nullable();
            $table->text('identity_reflection')->nullable();
            $table->text('action_reflection')->nullable();
            $table->text('overall_reflection')->nullable();
            $table->jsonb('domain_ratings')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'review_type', 'period_start']);
        });
    }
    public function down(): void { Schema::dropIfExists('reviews'); }
};
