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
        // Utiliser Schema::create() au lieu de Schema::table()
        Schema::create('companies', function (Blueprint $table) {
            $table->id('company_id');
            $table->string('company_email')->unique();
            $table->string('company_name');
            $table->string('company_logo')->nullable();
            $table->enum('plan_status', ['free', 'premium', 'entreprise', 'basic'])->default('free');
            $table->text('company_description')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('company_country')->default('Madagascar');
            $table->string('company_registration_number')->nullable();
            $table->string('company_tax_number')->nullable();
            $table->date('plan_start_date')->nullable();
            $table->date('plan_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('company_currency', 3)->default('MGA');
            $table->string('company_timezone')->default('Indian/Antananarivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
