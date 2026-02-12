<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('islamic_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 200);
            $table->string('title_ms', 200)->nullable();
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('hijri_date', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('islamic_events'); }
};
