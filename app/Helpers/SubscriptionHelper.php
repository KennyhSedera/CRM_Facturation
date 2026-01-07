<?php

namespace App\Helpers;

class SubscriptionHelper
{
    /**
     * Obtenir le prix d'un plan (en FCFA)
     */
    public static function getPlanPrice(string $plan): int
    {
        $planKey = strtoupper($plan);
        $price = config("subscription.plans.$planKey.price", 0);

        // Convertir le prix décimal en entier (9.900 -> 9900)
        return (int) ($price * 1000);
    }

    /**
     * Obtenir le prix formaté
     */
    public static function getFormattedPrice(string $plan): string
    {
        $price = self::getPlanPrice($plan);
        $currency = config('subscription.currency', 'FCFA');

        return number_format($price, 0, ',', ' ') . ' ' . $currency;
    }

    /**
     * Obtenir les limites d'un plan
     */
    public static function getPlanLimits(string $plan): array
    {
        $planKey = strtoupper($plan);
        return config("subscription.plans.$planKey.limits", []);
    }

    /**
     * Obtenir les features d'un plan
     */
    public static function getPlanFeatures(string $plan): array
    {
        $planKey = strtoupper($plan);
        return config("subscription.plans.$planKey.features", []);
    }

    /**
     * Obtenir l'icône d'un plan
     */
    public static function getPlanIcon(string $plan): string
    {
        $planKey = strtoupper($plan);
        return config("subscription.plans.$planKey.icon", '📦');
    }

    /**
     * Obtenir le nom d'un plan
     */
    public static function getPlanName(string $plan): string
    {
        $planKey = strtoupper($plan);
        return config("subscription.plans.$planKey.name", ucfirst($plan));
    }

    /**
     * Vérifier si une limite est atteinte
     */
    public static function hasReachedLimit(string $plan, string $limitType, int $currentValue): bool
    {
        $limits = self::getPlanLimits($plan);
        $limit = $limits[$limitType] ?? 0;

        // null = illimité
        if ($limit === null) {
            return false;
        }

        return $currentValue >= $limit;
    }

    /**
     * Obtenir le nombre max de clients pour un plan
     */
    public static function getMaxClients(string $plan): int
    {
        $limits = self::getPlanLimits($plan);
        $max = $limits['clients_max'] ?? 3;

        // null = illimité, on retourne un grand nombre
        return $max === null ? 999999 : $max;
    }

    /**
     * Obtenir le nombre max de devis par mois
     */
    public static function getMaxQuotesPerMonth(string $plan): int
    {
        $limits = self::getPlanLimits($plan);
        $max = $limits['quotes_per_month'] ?? 5;

        return $max === null ? 999999 : $max;
    }

    /**
     * Vérifier si une feature est disponible
     */
    public static function hasFeature(string $plan, string $feature): bool
    {
        $features = self::getPlanFeatures($plan);
        return $features[$feature] ?? false;
    }

    /**
     * Obtenir tous les plans disponibles
     */
    public static function getAvailablePlans(): array
    {
        return config('subscription.plans', []);
    }

    /**
     * Obtenir les prix de tous les plans
     */
    public static function getAllPrices(): array
    {
        $plans = ['free', 'premium', 'enterprise'];
        $prices = [];

        foreach ($plans as $plan) {
            $prices[$plan] = self::getPlanPrice($plan);
        }

        return $prices;
    }

    /**
     * Formater un prix générique
     */
    public static function formatPrice(float $price): string
    {
        $currency = config('subscription.currency', 'FCFA');
        return number_format($price * 1000, 0, ',', ' ') . ' ' . $currency;
    }

    /**
     * Obtenir la description d'un plan pour Telegram
     */
    public static function getPlanDescription(string $plan): string
    {
        $planKey = strtoupper($plan);
        $icon = self::getPlanIcon($plan);
        $name = self::getPlanName($plan);
        $price = self::getFormattedPrice($plan);
        $limits = self::getPlanLimits($plan);
        $features = self::getPlanFeatures($plan);

        $desc = "{$icon} <b>Plan {$name}</b>\n\n";

        if ($plan !== 'free') {
            $desc .= "💰 Prix : <b>{$price}/mois</b>\n\n";
        }

        $desc .= "📊 <b>Limites :</b>\n";

        $clientsMax = $limits['clients_max'] ?? 0;
        $desc .= "• Clients : " . ($clientsMax === null ? "illimités" : $clientsMax) . "\n";

        $quotesMax = $limits['quotes_per_month'] ?? 0;
        $desc .= "• Devis/mois : " . ($quotesMax === null ? "illimités" : $quotesMax) . "\n";

        $teamMax = $limits['team_members'] ?? 0;
        $desc .= "• Membres équipe : " . ($teamMax === null ? "illimités" : $teamMax) . "\n";

        $desc .= "\n✨ <b>Fonctionnalités :</b>\n";

        $featuresText = [
            'custom_logo' => 'Logo personnalisé',
            'custom_products' => 'Produits personnalisés',
            'advanced_stats' => 'Statistiques avancées',
            'multi_currency' => 'Multi-devises',
            'api_access' => 'Accès API',
            'priority_support' => 'Support prioritaire',
            'white_label' => 'Marque blanche',
        ];

        foreach ($featuresText as $key => $label) {
            if (isset($features[$key]) && $features[$key]) {
                $desc .= "• {$label}\n";
            }
        }

        return $desc;
    }
}

// Fonctions helper globales
if (!function_exists('plan_price')) {
    function plan_price(string $plan): int
    {
        return \App\Helpers\SubscriptionHelper::getPlanPrice($plan);
    }
}

if (!function_exists('plan_price_formatted')) {
    function plan_price_formatted(string $plan): string
    {
        return \App\Helpers\SubscriptionHelper::getFormattedPrice($plan);
    }
}

if (!function_exists('plan_icon')) {
    function plan_icon(string $plan): string
    {
        return \App\Helpers\SubscriptionHelper::getPlanIcon($plan);
    }
}

if (!function_exists('has_plan_feature')) {
    function has_plan_feature(string $plan, string $feature): bool
    {
        return \App\Helpers\SubscriptionHelper::hasFeature($plan, $feature);
    }
}
