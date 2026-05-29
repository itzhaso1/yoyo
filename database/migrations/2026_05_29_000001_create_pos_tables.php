<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('section', ['indoor', 'outdoor', 'vip'])->default('indoor')->index();
            $table->enum('status', ['free', 'busy', 'reserved', 'billing'])->default('free')->index();
            $table->unsignedSmallInteger('seats')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_tables');
    }
};
