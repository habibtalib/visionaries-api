<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('prompt')->nullable();
            $table->text('content');
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('journal_entries'); }
};
