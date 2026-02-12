<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->integer('version_number');
            $table->text('akhirah_intention')->nullable();
            $table->text('future_world')->nullable();
            $table->text('legacy')->nullable();
            $table->text('generated_statement')->nullable();
            $table->text('be_statement')->nullable();
            $table->text('change_summary')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'version_number']);
        });
    }
    public function down(): void { Schema::dropIfExists('vision_versions'); }
};
