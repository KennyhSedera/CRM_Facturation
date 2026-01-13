import { Head } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

// // declare global {
//     interface Window {
//         Telegram?: {
//             WebApp?: {
//                 ready: () => void;
//                 expand: () => void;
//                 close: () => void;
//                 sendData: (data: string) => void;
//                 enableClosingConfirmation: () => void;
//                 MainButton: {
//                     setText: (text: string) => void;
//                     show: () => void;
//                     hide: () => void;
//                     showProgress: () => void;
//                     hideProgress: () => void;
//                     onClick: (callback: () => void) => void;
//                     offClick: (callback: () => void) => void;
//                     color: string;
//                     textColor: string;
//                 };
//                 themeParams?: {
//                     bg_color?: string;
//                     text_color?: string;
//                     hint_color?: string;
//                     link_color?: string;
//                     button_color?: string;
//                     button_text_color?: string;
//                     secondary_bg_color?: string;
//                 };
//                 initData?: string;
//                 initDataUnsafe?: {
//                     user?: {
//                         id: number;
//                         first_name: string;
//                         last_name?: string;
//                         username?: string;
//                     };
//                 };
//                 showAlert: (message: string) => void;
//             };
//         };
//     }
// }

interface ClientFormTelegramProps {
    telegram_id?: number;
}

interface FormData {
    client_name: string;
    client_email: string;
    client_phone?: string;
    client_cin?: string;
    client_address?: string;
}

interface FormErrors {
    client_name?: string;
    client_email?: string;
    client_phone?: string;
    client_cin?: string;
    client_address?: string;
}

export default function ClientFormTelegram({ telegram_id }: ClientFormTelegramProps) {
    const [formData, setFormData] = React.useState<FormData>({
        client_name: '',
        client_email: '',
        client_phone: '',
        client_cin: '',
        client_address: '',
    });

    const [errors, setErrors] = React.useState<FormErrors>({});
    const [isLoading, setIsLoading] = useState(false);

    type TelegramWebApp = NonNullable<NonNullable<typeof window.Telegram>['WebApp']>;

    const [tg, setTg] = useState<TelegramWebApp | null>(null);
    const [showFallbackButton, setShowFallbackButton] = useState(false);

    const formDataRef = useRef(formData);

    useEffect(() => {
        formDataRef.current = formData;
    }, [formData]);

    useEffect(() => {
        const telegram = window.Telegram?.WebApp;

        if (telegram) {
            setTg(telegram);

            telegram.ready();
            telegram.expand();
            telegram.enableClosingConfirmation();

            document.body.style.backgroundColor = telegram.themeParams?.bg_color || '#ffffff';
            document.body.style.color = telegram.themeParams?.text_color || '#000000';

            telegram.MainButton.setText('✅ Créer le client');
            telegram.MainButton.color = telegram.themeParams?.button_color || '#3390ec';
            telegram.MainButton.textColor = telegram.themeParams?.button_text_color || '#ffffff';
            telegram.MainButton.show();

            const handleSubmitWrapper = () => {
                handleSubmitAction(telegram);
            };

            telegram.MainButton.onClick(handleSubmitWrapper);

            setTimeout(() => {
                setShowFallbackButton(true);
            }, 1000);

            return () => {
                telegram.MainButton.offClick(handleSubmitWrapper);
                telegram.MainButton.hide();
            };
        } else {
            setShowFallbackButton(true);
        }
    }, []);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));

        if (errors[name as keyof FormErrors]) {
            setErrors((prev) => ({
                ...prev,
                [name]: undefined,
            }));
        }
    };

    const validateFormData = (data: FormData): FormErrors => {
        const newErrors: FormErrors = {};
        if (!data.client_name.trim()) {
            newErrors.client_name = 'Le nom du client est requis.';
        }
        if (!data.client_email.trim()) {
            newErrors.client_email = "L'email du client est requis.";
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.client_email)) {
            newErrors.client_email = "L'email du client n'est pas valide.";
        }
        if (!data.client_phone?.trim()) {
            newErrors.client_phone = 'Le téléphone du client est requis.';
        }
        return newErrors;
    };

    const handleSubmitAction = (telegramApp: TelegramWebApp | null | undefined) => {
        const currentData = formDataRef.current;
        const validationErrors = validateFormData(currentData);

        if (Object.keys(validationErrors).length > 0) {
            setErrors(validationErrors);

            if (telegramApp) {
                telegramApp.showAlert('⚠️ Veuillez corriger les erreurs dans le formulaire');
            } else {
                alert('⚠️ Veuillez corriger les erreurs dans le formulaire');
            }
            return;
        }

        setIsLoading(true);
        telegramApp?.MainButton.showProgress();

        try {
            const dataToSend = JSON.stringify(currentData);

            if (telegramApp) {
                const userId = telegramApp.initDataUnsafe?.user?.id || telegram_id;
                const endpoint = `/api/telegram/client/create/${userId}`;

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: dataToSend,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        console.log('Response data:', data);

                        if (data.success) {
                            telegramApp.showAlert('✅ Entreprise créée avec succès!');
                            setTimeout(() => {
                                telegramApp.close();
                            }, 1000);
                        } else {
                            if (data.errors && typeof data.errors === 'object') {
                                Object.keys(data.errors).forEach((field) => {
                                    const messages = data.errors[field];
                                    if (Array.isArray(messages)) {
                                        setErrors((prev) => ({
                                            ...prev,
                                            [field]: messages.join(', '),
                                        }));
                                    } else {
                                        setErrors((prev) => ({
                                            ...prev,
                                            [field]: messages,
                                        }));
                                    }
                                });
                            } else if (data.message) {
                                telegramApp.showAlert('Erreur : ' + data.message);
                            } else {
                                telegramApp.showAlert('Erreur inconnue');
                            }

                            setIsLoading(false);
                            telegramApp.MainButton.hideProgress();
                        }
                    })
                    .catch((error) => {
                        telegramApp.showAlert('❌ Une erreur est survenue: ' + error);
                        telegramApp.MainButton.hideProgress();
                        setIsLoading(false);
                        telegramApp.close();
                    });
            } else {
                alert('✅ Entreprise créée! (Mode test)\n\nDonnées: ' + dataToSend);
                setIsLoading(false);
            }
        } catch (error) {
            if (telegramApp) {
                telegramApp.showAlert('❌ Une erreur est survenue: ' + error);
                telegramApp.MainButton.hideProgress();
            } else {
                alert('❌ Une erreur est survenue: ' + error);
            }

            setIsLoading(false);
        }
    };

    return (
        <>
            <Head title="Créer mon entreprise" />

            <div
                className="min-h-screen p-4"
                style={{
                    backgroundColor: tg?.themeParams?.bg_color || '#ffffff',
                    color: tg?.themeParams?.text_color || '#000000',
                }}
            >
                <div className="mx-auto max-w-2xl">
                    <div className="mb-6 text-center">
                        <h1 className="mb-2 text-3xl font-bold">🏢 Créer mon entreprise</h1>
                        <p className="opacity-70">Remplissez les informations de votre entreprise</p>
                    </div>

                    <div className="space-y-5">
                        <div>
                            <label htmlFor="client_name" className="mb-2 block text-sm font-semibold">
                                Nom du client <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="client_name"
                                name="client_name"
                                value={formData.client_name}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions Madagascar"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.client_name ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.client_name && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.client_name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="client_email" className="mb-2 block text-sm font-semibold">
                                Email du client <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="client_email"
                                name="client_email"
                                value={formData.client_email}
                                onChange={handleChange}
                                placeholder="Ex: 4Hs9O@example.com"
                                className="w-full rounded-xl border-2 border-gray-200 px-4 py-3 transition-all focus:border-blue-500"
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.client_email && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.client_email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="client_phone" className="mb-2 block text-sm font-semibold">
                                Téléphone du client <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="tel"
                                id="client_phone"
                                name="client_phone"
                                value={formData.client_phone}
                                onChange={handleChange}
                                placeholder="Ex: +261 34 12 345 67"
                                className="w-full rounded-xl border-2 border-gray-200 px-4 py-3 transition-all focus:border-blue-500"
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.client_phone && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.client_phone}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="client_cin" className="mb-2 block text-sm font-semibold">
                                CIN du client
                            </label>
                            <input
                                type="text"
                                id="client_cin"
                                name="client_cin"
                                value={formData.client_cin}
                                onChange={handleChange}
                                placeholder="Ex: 1 23 456 789"
                                className="w-full rounded-xl border-2 border-gray-200 px-4 py-3 transition-all focus:border-blue-500"
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.client_cin && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.client_cin}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="client_address" className="mb-2 block text-sm font-semibold">
                                Adresse du client
                            </label>
                            <input
                                type="text"
                                id="client_address"
                                name="client_address"
                                value={formData.client_address}
                                onChange={handleChange}
                                placeholder="Ex: 123 Rue de l'Innovation, Antananarivo"
                                className="w-full rounded-xl border-2 border-gray-200 px-4 py-3 transition-all focus:border-blue-500"
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.client_address && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.client_address}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="mt-8 rounded-xl border-2 border-blue-200 bg-blue-50 p-4">
                        <p className="flex items-start gap-2 text-sm text-blue-800">
                            <span className="text-lg">ℹ️</span>
                            <span>
                                {showFallbackButton
                                    ? 'Cliquez sur le bouton ci-dessus pour créer votre entreprise'
                                    : "Cliquez sur le bouton en bas de l'écran pour créer votre entreprise"}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
