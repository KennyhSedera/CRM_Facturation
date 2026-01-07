<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1️⃣ Supprimer l’ancienne contrainte
        DB::statement("
            ALTER TABLE companies
            DROP CONSTRAINT IF EXISTS companies_plan_status_check
        ");

        // 2️⃣ Mettre à jour les anciennes valeurs
        DB::statement("
            UPDATE companies
            SET plan_status = 'free'
            WHERE plan_status IN ('trial', 'expired', 'cancelled')
        ");

        DB::statement("
            UPDATE companies
            SET plan_status = 'basic'
            WHERE plan_status = 'active'
        ");

        // 3️⃣ Ajouter la nouvelle contrainte
        DB::statement("
            ALTER TABLE companies
            ADD CONSTRAINT companies_plan_status_check
            CHECK (plan_status IN ('free', 'basic', 'premium', 'enterprise'))
        ");

        // 4️⃣ Mettre à jour la valeur par défaut
        DB::statement("
            ALTER TABLE companies
            ALTER COLUMN plan_status SET DEFAULT 'free'
        ");
    }

    public function down(): void
    {
        // Supprimer la nouvelle contrainte
        DB::statement("
            ALTER TABLE companies
            DROP CONSTRAINT IF EXISTS companies_plan_status_check
        ");

        // Restaurer l’ancienne contrainte
        DB::statement("
            ALTER TABLE companies
            ADD CONSTRAINT companies_plan_status_check
            CHECK (plan_status IN ('trial', 'active', 'expired', 'cancelled'))
        ");

        DB::statement("
            ALTER TABLE companies
            ALTER COLUMN plan_status SET DEFAULT 'trial'
        ");
    }
};
