<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_variant_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_at_utc'); // ВСЕГДА в UTC
            $table->dateTime('end_at_utc');
            $table->string('client_name', 128);
            $table->string('client_phone', 64);
            $table->enum('status', ['active','canceled'])->default('active');

            $table->timestamps();

            $table->index(['service_variant_id','start_at_utc']);
            $table->index(['service_variant_id','end_at_utc']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
