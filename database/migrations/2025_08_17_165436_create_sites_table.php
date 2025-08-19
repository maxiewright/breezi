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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('slug');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('postcode');
            $table->string('city');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'city']);
            $table->index(['customer_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
