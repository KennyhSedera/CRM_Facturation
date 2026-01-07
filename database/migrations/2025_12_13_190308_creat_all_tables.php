<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table Company
        Schema::create('company', function (Blueprint $table) {
            $table->id('company_id');
            $table->string('company_email')->unique();
            $table->string('company_name');
            $table->string('company_logo')->nullable();
            $table->enum('plan_status', ['free', 'basic', 'premium', 'enterprise'])->default('free');
            $table->timestamps();
        });

        // Modification de la table Users existante
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('password')->constrained('company', 'company_id')->onDelete('set null')->onUpdate('cascade');
            $table->string('user_role')->default('user')->after('company_id');
        });

        // Table Clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id('client_id');
            $table->string('client_email');
            $table->string('client_name');
            $table->string('client_adress')->nullable();
            $table->string('client_cin', 50)->nullable();
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();

            $table->index('user_id');
        });

        // Table Articles
        Schema::create('articles', function (Blueprint $table) {
            $table->id('article_id');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->string('article_source')->nullable();
            $table->string('article_unité', 50)->nullable();
            $table->decimal('selling_price', 10, 2);
            $table->string('article_name');
            $table->string('article_reference', 100)->nullable();
            $table->decimal('article_tva', 5, 2)->default(0.00);
            $table->integer('quantity_stock')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        // Table MvtArticles
        Schema::create('mvt_articles', function (Blueprint $table) {
            $table->id('mvt_id');
            $table->foreignId('article_id')->constrained('articles', 'article_id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('mvtType', ['entree', 'sortie', 'ajustement', 'retour']);
            $table->integer('mvt_quantity');
            $table->date('mvt_date');
            $table->timestamp('created_at')->useCurrent();

            $table->index('article_id');
            $table->index('mvt_date');
        });

        // Table Quotes (Devis)
        Schema::create('quotes', function (Blueprint $table) {
            $table->id('quote_id');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('client_id')->constrained('clients', 'client_id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('vendeur_id')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->string('mode_paiement', 50)->nullable();
            $table->enum('quote_status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->date('quote_date');
            $table->timestamps();

            $table->index('client_id');
            $table->index('quote_date');
        });

        // Table QuoteItems
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id('qitem_id');
            $table->foreignId('quote_id')->constrained('quotes', 'quote_id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('article_id')->constrained('articles', 'article_id')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('tva', 5, 2)->default(0.00);
            $table->timestamps();

            $table->index('quote_id');
            $table->index('article_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('mvt_articles');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('clients');

        // Suppression des colonnes ajoutées à users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'user_role']);
        });

        Schema::dropIfExists('company');
    }
};
