import { Head } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

interface ArticleFormTelegramProps {
    telegram_id?: number;
}

interface FormData {
    article_name: string;
    selling_price: number;
    article_unité?: string;
    article_tva?: number;
    quantity_stock?: number;
    article_reference?: string;
}

interface FormErrors {
    article_name?: string;
    selling_price?: number;
    article_unité?: string;
    article_tva?: number;
    quantity_stock?: number;
    article_reference?: string;
}

export default function ArticleFormTelegram({ telegram_id }: ArticleFormTelegramProps) {
    const [formData, setFormData] = React.useState<FormData>({
        article_name: '',
        selling_price: 0,
        article_unité: '',
        article_tva: 0,
        quantity_stock: 0,
        article_reference: '',
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

            telegram.MainButton.setText('✅ Créer un article');
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
        if (!data.article_name.trim()) {
            newErrors.article_name = "Le nom de l'article est requis.";
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
                const endpoint = `/api/telegram/article/create/${userId}`;

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: dataToSend,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            telegramApp.showAlert('✅ Article créée avec succès!');
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
                alert('✅ Article créée! (Mode test)\n\nDonnées: ' + dataToSend);
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
            <Head title="Créer un article" />

            <div
                className="min-h-screen p-4"
                style={{
                    backgroundColor: tg?.themeParams?.bg_color || '#ffffff',
                    color: tg?.themeParams?.text_color || '#000000',
                }}
            >
                <div className="mx-auto max-w-2xl">
                    <div className="mb-6 text-center">
                        <h1 className="mb-2 text-3xl font-bold">🏢 Créer un article</h1>
                        <p className="opacity-70">Remplissez les informations de votre article</p>
                    </div>

                    <div className="space-y-5">
                        <div>
                            <label htmlFor="article_name" className="mb-2 block text-sm font-semibold">
                                Nom de l'article <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="article_name"
                                name="article_name"
                                value={formData.article_name}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions Madagascar"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.article_name ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.article_name && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.article_name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="article_reference" className="mb-2 block text-sm font-semibold">
                                Référence de l'article
                            </label>
                            <input
                                type="text"
                                id="article_reference"
                                name="article_reference"
                                value={formData.article_reference}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions Madagascar"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.article_reference ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.article_reference && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.article_reference}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="selling_price" className="mb-2 block text-sm font-semibold">
                                Prix unitaire de l'article <span className="text-red-500">*</span>
                            </label>
                            <div className="flex items-center justify-between">
                                <input
                                    type="text"
                                    id="selling_price"
                                    name="selling_price"
                                    value={formData.selling_price}
                                    onChange={handleChange}
                                    placeholder="Ex: 12000"
                                    className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                        errors.selling_price ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                    }`}
                                    style={{
                                        backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                        color: tg?.themeParams?.text_color || '#000000',
                                    }}
                                    disabled={isLoading}
                                />
                                <span>FCFA</span>
                            </div>
                            {errors.selling_price && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.selling_price}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="article_unité" className="mb-2 block text-sm font-semibold">
                                Unité de l'article <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="article_unité"
                                name="article_unité"
                                value={formData.article_unité}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions Madagascar"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.article_unité ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.article_unité && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.article_unité}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="article_tva" className="mb-2 block text-sm font-semibold">
                                TVA de l'article
                            </label>
                            <input
                                type="number"
                                id="article_tva"
                                name="article_tva"
                                value={formData.article_tva}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions Madagascar"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.article_tva ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.article_tva && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.article_tva}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="quantity_stock" className="mb-2 block text-sm font-semibold">
                                Quantité en stock
                            </label>
                            <input
                                type="number"
                                id="quantity_stock"
                                name="quantity_stock"
                                value={formData.quantity_stock}
                                onChange={handleChange}
                                placeholder="Ex: TechSolutions Madagascar"
                                className={`w-full rounded-xl border-2 px-4 py-3 transition-all ${
                                    errors.quantity_stock ? 'border-red-500 bg-red-50' : 'border-gray-200 focus:border-blue-500'
                                }`}
                                style={{
                                    backgroundColor: tg?.themeParams?.secondary_bg_color || '#f5f5f5',
                                    color: tg?.themeParams?.text_color || '#000000',
                                }}
                                disabled={isLoading}
                            />
                            {errors.quantity_stock && (
                                <p className="mt-1 flex items-center gap-1 text-sm text-red-500">
                                    <span>⚠️</span> {errors.quantity_stock}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="mt-8 rounded-xl border-2 border-blue-200 bg-blue-50 p-4">
                        <p className="flex items-start gap-2 text-sm text-blue-800">
                            <span className="text-lg">ℹ️</span>
                            <span>
                                {showFallbackButton
                                    ? 'Cliquez sur le bouton ci-dessus pour créer votre article'
                                    : "Cliquez sur le bouton en bas de l'écran pour créer votre article"}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
