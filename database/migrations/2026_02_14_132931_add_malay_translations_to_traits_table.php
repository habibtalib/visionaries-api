<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('traits', function (Blueprint $table) {
            $table->string('name_ms', 50)->nullable()->after('name');
            $table->text('description_ms')->nullable()->after('description');
            $table->text('why_template_ms')->nullable()->after('why_template');
            $table->text('daily_template_ms')->nullable()->after('daily_template');
            $table->text('opposite_template_ms')->nullable()->after('opposite_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traits', function (Blueprint $table) {
            $table->dropColumn([
                'name_ms', 
                'description_ms', 
                'why_template_ms', 
                'daily_template_ms', 
                'opposite_template_ms'
            ]);
        });
    }
};