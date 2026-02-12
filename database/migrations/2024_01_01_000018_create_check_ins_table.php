<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('check_in_date');
            $table->text('gratitude')->nullable();
            $table->text('struggle')->nullable();
            $table->text('dua')->nullable();
            $table->text('tawakkul_moment')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'check_in_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('check_ins'); }
};
