<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('saving_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit','withdraw']);
            $table->decimal('amount', 16, 2);
            $table->dateTime('transacted_at');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('saving_entries');
    }
};
