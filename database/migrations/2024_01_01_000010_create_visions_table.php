<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('akhirah_intention')->nullable();
            $table->text('future_world')->nullable();
            $table->text('legacy')->nullable();
            $table->text('generated_statement')->nullable();
            $table->text('be_statement')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('visions'); }
};
