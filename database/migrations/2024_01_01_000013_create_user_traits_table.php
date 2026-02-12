<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_traits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('trait_id')->nullable()->constrained('traits')->nullOnDelete();
            $table->string('custom_name', 50)->nullable();
            $table->text('why')->nullable();
            $table->text('daily')->nullable();
            $table->text('opposite')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'trait_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_traits'); }
};
