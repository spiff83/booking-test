<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0=вс ... 6=сб
            $table->time('start_local')->nullable(); // локальное время МСК
            $table->time('end_local')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->unique(['service_id','weekday']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('service_schedules');
    }
};
