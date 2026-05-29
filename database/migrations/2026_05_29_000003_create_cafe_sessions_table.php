<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cafe_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pos_table_id')->constrained('pos_tables')->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('open')->index();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable()->index();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tip', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['pos_table_id', 'status']);
            $table->index(['opened_at', 'closed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafe_sessions');
    }
};
