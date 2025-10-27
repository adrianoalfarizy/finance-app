<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama akun, mis: "Dompet", "BCA", "OVO"
            $table->enum('type', ['cash','bank','ewallet'])->default('cash');
            $table->string('currency', 3)->default('IDR');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('accounts');
    }
};
