<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Référence vers clients
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')
                ->references('client_id')
                ->on('clients')
                ->onDelete('cascade');

            // Référence vers users
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Référence vers quotes
            $table->unsignedBigInteger('quote_id')->nullable();
            $table->foreign('quote_id')
                ->references('quote_id')
                ->on('quotes')
                ->onDelete('set null');

            $table->date('date');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status', 50)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
