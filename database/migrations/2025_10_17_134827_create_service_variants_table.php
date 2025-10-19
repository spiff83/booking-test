<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');                 // "30 минут", "60 минут" и т.п.
            $table->unsignedSmallInteger('duration_min'); // 30/60/120
            $table->unsignedSmallInteger('padding_min')->default(30); // буфер из ТЗ
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('service_variants');
    }
};
