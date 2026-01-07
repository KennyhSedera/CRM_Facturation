// Types pour le statut du plan
export type PlanStatus = 'free' | 'basic' | 'premium' | 'enterprise';

// Interface pour une entreprise
export interface Company {
    company_id: number;
    company_name: string;
    company_email: string;
    company_logo: string | null;
    plan_status: PlanStatus;
    created_at: string;
    updated_at: string;
}

// Interface pour le formulaire de création/modification
export interface CompanyFormData {
    company_name: string;
    company_email: string;
    company_logo: File | null;
    plan_status: PlanStatus;
    [key: string]: string | File | null | PlanStatus;
}

// Interface pour la pagination des entreprises
export interface PaginatedCompanies {
    data: Company[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

// Interface pour les erreurs de validation
export interface CompanyErrors {
    company_name?: string;
    company_email?: string;
    company_logo?: string;
    plan_status?: string;
}

// Type pour les options de plan
export interface PlanOption {
    value: PlanStatus;
    label: string;
    icon: string;
    color: string;
}

// Constantes pour les couleurs des plans
export const PLAN_COLORS: Record<PlanStatus, string> = {
    free: 'bg-gray-100 text-gray-800',
    basic: 'bg-blue-100 text-blue-800',
    premium: 'bg-purple-100 text-purple-800',
    enterprise: 'bg-amber-100 text-amber-800',
};

// Constantes pour les labels des plans
export const PLAN_LABELS: Record<PlanStatus, string> = {
    free: 'Gratuit',
    basic: 'Basic',
    premium: 'Premium',
    enterprise: 'Enterprise',
};

// Options de plan pour les formulaires
export const PLAN_OPTIONS: PlanOption[] = [
    { value: 'free', label: 'Gratuit', icon: '🎁', color: 'indigo' },
    { value: 'basic', label: 'Basic', icon: '📦', color: 'blue' },
    { value: 'premium', label: 'Premium', icon: '⭐', color: 'purple' },
    { value: 'enterprise', label: 'Enterprise', icon: '👑', color: 'amber' },
];
