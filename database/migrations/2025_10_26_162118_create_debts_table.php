<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete(); // asal dana/akun terkait hutang
            $table->string('creditor_name'); // kepada siapa berhutang
            $table->decimal('principal_amount', 16, 2);
            $table->decimal('interest_rate', 5, 2)->nullable(); // opsional
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['ongoing','paid'])->default('ongoing');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('debts');
    }
};
