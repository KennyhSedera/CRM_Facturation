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
        Schema::table('company', function (Blueprint $table) {
            $table->text('company_description')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('company_country')->default('Togo');
            $table->string('company_registration_number')->nullable();
            $table->string('company_tax_number')->nullable();
            $table->date('plan_start_date')->nullable();
            $table->date('plan_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('company_currency', 3)->default('FCA');
            $table->string('company_timezone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn([
                'company_description',
                'company_phone',
                'company_website',
                'company_address',
                'company_city',
                'company_postal_code',
                'company_country',
                'company_registration_number',
                'company_tax_number',
                'plan_start_date',
                'plan_end_date',
                'is_active',
                'company_currency',
                'company_timezone',
            ]);
        });
    }
};
