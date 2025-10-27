<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete(); // transaksi milik akun siapa
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // siapa yang input
            $table->enum('type', ['income','expense']); // transfer diwakili 2 baris: expense di sumber + income di tujuan
            $table->uuid('group_id')->nullable(); // untuk mengikat pasangan transfer
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 16, 2);
            $table->dateTime('transacted_at');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['account_id','transacted_at']);
            $table->index('group_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('transactions');
    }
};
