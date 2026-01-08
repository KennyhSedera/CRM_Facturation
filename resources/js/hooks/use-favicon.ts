// resources/js/hooks/use-favicon.ts

import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';


export const useFavicon = () => {
    const { auth } = usePage<PageProps>().props;

    useEffect(() => {
        const favicon = document.querySelector("link[rel='icon']") as HTMLLinkElement;

        if (!favicon) return;

        // Si l'utilisateur est connecté et a un logo d'entreprise
        if (auth.user?.company?.company_logo) {
            const logoUrl = `/storage/${auth.user.company.company_logo}`;
            favicon.href = logoUrl;

            // Sauvegarder dans localStorage pour les futures visites
            localStorage.setItem('company_logo', logoUrl);
        } else {
            // Utiliser le favicon par défaut
            favicon.href = '/facture-pro.png';
            localStorage.removeItem('company_logo');
        }
    }, [auth.user]);
};

// Fonction d'initialisation pour app.tsx (avant le montage de React)
export const initializeFavicon = () => {
    const favicon = document.querySelector("link[rel='icon']") as HTMLLinkElement;

    if (favicon) {
        // Charger le logo sauvegardé depuis localStorage
        const savedLogo = localStorage.getItem('company_logo');
        if (savedLogo) {
            favicon.href = savedLogo;
        }
    }
};
