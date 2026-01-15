<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // Référence vers invoices
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->onDelete('cascade');

            // Référence vers articles
            $table->unsignedBigInteger('article_id');
            $table->foreign('article_id')
                ->references('article_id')
                ->on('articles')
                ->onDelete('cascade');

            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
