<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            // Contact
            $table->string('client_phone', 20)->nullable()->after('client_email');

            // Adresse détaillée
            $table->string('client_city')->nullable()->after('client_adress');
            $table->string('client_country')->nullable()->after('client_city');

            // Gestion & suivi
            $table->enum('client_status', ['active', 'inactive'])
                ->default('active')
                ->after('client_cin');

            $table->text('client_note')->nullable()->after('client_status');

            // Référence client (pour devis / factures)
            $table->string('client_reference')->nullable()->unique()->after('client_note');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'client_phone',
                'client_city',
                'client_country',
                'client_status',
                'client_note',
                'client_reference',
            ]);
        });
    }
};
