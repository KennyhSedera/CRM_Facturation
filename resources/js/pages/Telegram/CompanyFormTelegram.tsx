// resources/js/Pages/Telegram/CompanyFormTelegram.tsx

import { Head } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

// Définir le type pour Telegram WebApp
declare global {
    interface Window {
        Telegram?: {
            WebApp?: {
                ready: () => void;
                expand: () => void;
                close: () => void;
                sendData: (data: string) => void;
                enableClosingConfirmation: () => void;
                MainButton: {
                    setText: (text: string) => void;
                    show: () => void;
                    hide: () => void;
                    showProgress: () => void;
                    hideProgress: () => void;
                    onClick: (callback: () => void) => void;
                    offClick: (callback: () => void) => void;
                    color: string;
                    textColor: string;
                };
                themeParams?: {
                    bg_color?: string;
                    text_color?: string;
                    hint_color?: string;
                    link_color?: string;
                    button_color?: string;
                    button_text_color?: string;
                    secondary_bg_color?: string;
                };
                initData?: string;
                initDataUnsafe?: {
                    user?: {
                        id: number;
                        first_name: string;
                        last_name?: string;
                        username?: string;
                    };
                };
                showAlert: (message: string) => void;
            };
        };
    }
}

interface FormData {
    company_name: string;
    company_email: string;
    company_description: string;
    company_phone: string;
    company_website: string;
    company_address: string;
}

interface FormErrors {
    company_name?: string;
    company_email?: string;
    company_description?: string;
    company_phone?: string;
    company_address?: string;
}

interface CompanyFormTelegramProps {
    telegram_id?: number;
}

export default function CompanyFormTelegram({ telegram_id }: CompanyFormTelegramProps) {
    const [formData, setFormData] = useState<FormData>({
        company_name: '',
        company_email: '',
        company_description: '',
        company_phone: '',
        company_website: '',
        company_address: '',
    });

    const [errors, setErrors] = useState<FormErrors>({});
    const [isLoading, setIsLoading] = useState(false);
    const [tg, setTg] = useState<NonNullable<typeof window.Telegram>['WebApp'] | null>(null);
    const [showFallbackButton, setShowFallbackButton] = useState(false);

    // Référence pour toujours avoir les dernières données
    const formDataRef = useRef(formData);

    // Mettre à jour la référence quand formData change
    useEffect(() => {
        formDataRef.current = formData;
    }, [formData]);

    useEffect(() => {
        const telegram = window.Telegram?.WebApp;

        if (telegram) {
            setTg(telegram);

            // Configuration initiale
            telegram.ready();
            telegram.expand();
            telegram.enableClosingConfirmation();

            // Adapter aux couleurs du thème
            document.body.style.backgroundColor = telegram.themeParams?.bg_color || '#ffffff';
            document.body.style.color = telegram.themeParams?.text_color || '#000000';

            // Configurer le bouton principal
            telegram.MainButton.setText("✅ Créer l'entreprise");
            telegram.MainButton.color = telegram.themeParams?.button_color || '#3390ec';
            telegram.MainButton.textColor = telegram.themeParams?.button_text_color || '#ffffff';
            telegram.MainButton.show();

            // Handler qui utilise la référence
            const handleSubmitWrapper = () => {
                handleSubmitAction();
            };

            telegram.MainButton.onClick(handleSubmitWrapper);

            // Vérifier si le MainButton s'affiche après 1 seconde
            setTimeout(() => {
                setShowFallbackButton(true);
            }, 1000);

            return () => {
                telegram.MainButton.offClick(handleSubmitWrapper);
                telegram.MainButton.hide();
            };
        } else {
            console.warn('Telegram WebApp non disponible');
            setShowFallbackButton(true);
        }
    }, []);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));

        // Effacer l'erreur du champ
        if (errors[name as keyof FormErrors]) {
            setErrors((prev) => ({
                ...prev,
                [name]: undefined,
            }));
        }
    };

    const validateFormData = (data: FormData): FormErrors => {
        const newErrors: FormErrors = {};

        // Validation du nom
        if (!data.company_name.trim()) {
            newErrors.company_name = "Le nom de l'entreprise est requis";
        } else if (data.company_name.trim().length < 2) {
            newErrors.company_name = 'Le nom doit contenir au moins 2 caractères';
        }

        // Validation de l'email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!data.company_email.trim()) {
            newErrors.company_email = "L'email est requis";
        } else if (!emailRegex.test(data.company_email)) {
            newErrors.company_email = "L'email doit être valide";
        }

        // Validation de la description
        if (!data.company_description.trim()) {
            newErrors.company_description = 'La description est requise';
        } else if (data.company_description.trim().length < 10) {
            newErrors.company_description = 'La description doit contenir au moins 10 caractères';
        }

        // Validation du téléphone
        if (!data.company_phone.trim()) {
            newErrors.company_phone = 'Le téléphone est requis';
        } else if (data.company_phone.trim().length < 8) {
            newErrors.company_phone = 'Le téléphone doit contenir au moins 8 caractères';
        }

        // Validation de l'adresse
        if (!data.company_address.trim()) {
            newErrors.company_address = "L'adresse est requise";
        } else if (data.company_address.trim().length < 5) {
            newErrors.company_address = "L'adresse doit contenir au moins 5 caractères";
        }

        return newErrors;
    };

    const handleSubmitAction = () => {
        // Utiliser formDataRef.current pour avoir les données à jour
        const currentData = formDataRef.current;

        console.log('=== SUBMIT ===');
        console.log('TG disponible?', !!tg);
        console.log('Données actuelles:', currentData);

        // Valider les données
        const validationErrors = validateFormData(currentData);

        if (Object.keys(validationErrors).length > 0) {
            console.log('Erreurs de validation:', validationErrors);
            setErrors(validationErrors);

            if (tg) {
                tg.showAlert('⚠️ Veuillez corriger les erreurs dans le formulaire');
            } else {
                alert('⚠️ Veuillez corriger les erreurs dans le formulaire');
            }
            return;
        }

        console.log('Validation OK, envoi des données...');
        setIsLoading(true);
        tg?.MainButton.showProgress();

        try {
            const dataToSend = JSON.stringify(currentData);
            console.log('Données à envoyer:', dataToSend);

            if (tg) {
                console.log('Envoi via Telegram WebApp...');
                tg.sendData(dataToSend);
                console.log('Données envoyées, fermeture dans 1s...');

                setTimeout(() => {
                    console.log('Fermeture de la WebApp');
                    tg.close();
                }, 1000);
            } else {
                console.error('Telegram WebApp non disponible!');
                alert('❌ Telegram WebApp non disponible. Données: ' + dataToSend);
                setIsLoading(false);
            }
        } catch (error) {
            console.error("Erreur lors de l'envoi:", error);
            if (tg) {
                tg.showAlert('❌ Une erreur est survenue: ' + error);
            } else {
                alert('❌ Une erreur est survenue: ' + error);
            }
            tg?.MainButton.hideProgress();
            setIsLoading(false);
        }
    };

    const handleSubmit = () => {
        handleSubmitAction();
    };

    return (
        <>
            <Head title="Créer une entreprise" />

            <div
                className="min-h-screen p-6 pb-24"
                style={{
                    backgroundColor: tg?.themeParams?.bg_color || '#ffffff',
                    color: tg?.themeParams?.text_color || '#000000',
                }}
            >
                <div className="mx-auto max-w-2xl">
                    {/* En-tête */}
                    <div className="mb-8">
                        <h1 className="mb-2 text-3xl font-bold">🏢 Créer mon entreprise</h1>
                        <p className="text-sm opacity-70">Remplissez les informations de votre entreprise</p>
                    </div>

                    <div className="space-y-5">
                        {/* Nom de l'entreprise */}
                        <div>
                            <label htmlFor="company_name" className="mb-2 block text-sm font-semibold">
                                Nom de l'entreprise <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value={formData.company_name}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions SARL"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.company_name ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.company_name && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.company_name}
                                </p>
                            )}
                        </div>

                        {/* Email */}
                        <div>
                            <label htmlFor="company_email" className="mb-2 block text-sm font-semibold">
                                Email professionnel <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="company_email"
                                name="company_email"
                                value={formData.company_email}
                                onChange={handleChange}
                                placeholder="Ex: contact@techsolutions.mg"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.company_email ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.company_email && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.company_email}
                                </p>
                            )}
                        </div>

                        {/* Description */}
                        <div>
                            <label htmlFor="company_description" className="mb-2 block text-sm font-semibold">
                                Description de l'activité <span className="text-red-500">*</span>
                            </label>
                            <textarea
                                id="company_description"
                                name="company_description"
                                value={formData.company_description}
                                onChange={handleChange}
                                placeholder="Ex: Développement de solutions web et mobile pour entreprises"
                                rows={4}
                                className={`w-full resize-y rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.company_description ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.company_description && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.company_description}
                                </p>
                            )}
                            <p className="mt-1 text-xs opacity-60">{formData.company_description.length} / 500 caractères</p>
                        </div>

                        {/* Téléphone */}
                        <div>
                            <label htmlFor="company_phone" className="mb-2 block text-sm font-semibold">
                                Téléphone <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="tel"
                                id="company_phone"
                                name="company_phone"
                                value={formData.company_phone}
                                onChange={handleChange}
                                placeholder="Ex: +261 34 12 345 67"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.company_phone ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.company_phone && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.company_phone}
                                </p>
                            )}
                        </div>

                        {/* Site web (optionnel) */}
                        <div>
                            <label htmlFor="company_website" className="mb-2 block text-sm font-semibold">
                                Site web <span className="text-gray-400">(optionnel)</span>
                            </label>
                            <input
                                type="url"
                                id="company_website"
                                name="company_website"
                                value={formData.company_website}
                                onChange={handleChange}
                                placeholder="Ex: www.techsolutions.mg"
                                className="w-full rounded-xl border-2 border-gray-200 px-4 py-3 transition-all focus:border-blue-500"
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                        </div>

                        {/* Adresse */}
                        <div>
                            <label htmlFor="company_address" className="mb-2 block text-sm font-semibold">
                                Adresse complète <span className="text-red-500">*</span>
                            </label>
                            <textarea
                                id="company_address"
                                name="company_address"
                                value={formData.company_address}
                                onChange={handleChange}
                                placeholder="Ex: Lot II A 45, Antananarivo 101"
                                rows={3}
                                className={`w-full resize-y rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.company_address ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.company_address && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.company_address}
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Info en bas */}
                    <div className="mt-8 rounded-xl border-2 border-blue-200 bg-blue-50 p-4">
                        <p className="flex items-start gap-2 text-sm text-blue-800">
                            <span className="text-lg">ℹ️</span>
                            <span>
                                {showFallbackButton
                                    ? 'Cliquez sur le bouton ci-dessous pour créer votre entreprise'
                                    : "Cliquez sur le bouton en bas de l'écran pour créer votre entreprise"}
                            </span>
                        </p>
                    </div>

                    {/* BOUTON DE SECOURS */}
                    {showFallbackButton && (
                        <div className="fixed right-0 bottom-0 left-0 border-t-2 border-gray-200 bg-white p-4 shadow-lg">
                            <button
                                onClick={handleSubmit}
                                disabled={isLoading}
                                className="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-500 px-6 py-4 font-bold text-white transition-colors hover:bg-blue-600 active:bg-blue-700 disabled:bg-gray-400"
                            >
                                {isLoading ? (
                                    <>
                                        <svg className="h-5 w-5 animate-spin" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            />
                                        </svg>
                                        <span>Création en cours...</span>
                                    </>
                                ) : (
                                    <>
                                        <span>✅</span>
                                        <span>Créer l'entreprise</span>
                                    </>
                                )}
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
