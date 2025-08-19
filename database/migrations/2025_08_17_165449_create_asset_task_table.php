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
        Schema::create('asset_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->text('service_notes')->nullable();
            $table->string('condition_before')->nullable(); // e.g., 'good', 'fair', 'poor'
            $table->string('condition_after')->nullable();
            $table->boolean('filter_changed')->default(false);
            $table->boolean('parts_replaced')->default(false);
            $table->text('parts_list')->nullable();
            $table->decimal('labor_hours', 4, 2)->nullable();
            $table->timestamps();

            $table->unique(['asset_id', 'task_id']);
            $table->index(['task_id', 'asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_task');
    }
};
