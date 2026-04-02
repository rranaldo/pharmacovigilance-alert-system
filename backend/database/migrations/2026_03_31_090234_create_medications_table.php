<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lot_number', 50)->index();
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            // Lot numbers are not unique per se — different medications can share a lot
            // from the same manufacturing batch, so we only index, not unique constraint
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
