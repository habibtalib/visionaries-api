<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('action_check_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('action_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('check_in_date');
            $table->string('status', 10);
            $table->text('reflection')->nullable();
            $table->string('mood', 20)->nullable();
            $table->integer('energy_level')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['action_id', 'check_in_date']);
            $table->index(['user_id', 'check_in_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('action_check_ins'); }
};
