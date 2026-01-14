import espece from '@/assets/espece.jpg';
import orange from '@/assets/orange.png';
// import paypal from '@/assets/paypal.png';
import wave from '@/assets/wave.webp';
import { Button } from '@/components/ui/button';
import CardPaiement from '@/components/ui/card-paiement';
import DateInput from '@/components/ui/date-input';
import FileInput from '@/components/ui/file-input';
import SelectInput from '@/components/ui/SelectInput';
import TextArea from '@/components/ui/text-area';
import TextInput from '@/components/ui/text-input';
import { addOneMonth } from '@/lib/utils';
import { Head, useForm, usePage } from '@inertiajs/react';
import axios from 'axios';
import { Building2, Info, MapPin, Shield } from 'lucide-react';
import { useState } from 'react';
import { GiReceiveMoney } from 'react-icons/gi';
import { IoIosCloseCircle } from 'react-icons/io';

// Types
type PlanStatus = 'free' | 'basic' | 'premium' | 'enterprise';

interface PaiementOption {
    value: string;
    label: string;
    icon: string;
}

const PAIMENT_OPTIONS: PaiementOption[] = [
    { value: 'espece', label: 'En espèce', icon: espece },
    // { value: 'paypal', label: 'Paypal', icon: paypal },
    { value: 'wave', label: 'Wave', icon: wave },
    { value: 'orange_money', label: 'Orange Money', icon: orange },
    { value: 'autre', label: 'Autre', icon: '🏢' },
];

export default function Create() {
    const { url } = usePage();
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [currentStep, setCurrentStep] = useState(1);
    const [submitError, setSubmitError] = useState<string | null>(null);
    const [submitSuccess, setSubmitSuccess] = useState<string | null>(null);

    const plan: PlanStatus = new URLSearchParams(url.split('?')[1]).get('plan') as PlanStatus;

    const { data, setData, post, processing, errors, setError } = useForm({
        company_name: '',
        company_email: '',
        company_logo: null as File | null,
        plan_status: plan,

        company_description: '',
        company_phone: '',
        company_website: '',
        company_address: '',
        company_city: '',
        company_postal_code: '',
        company_country: 'Togo',

        company_registration_number: '',
        company_tax_number: '',

        plan_start_date: new Date().toISOString().split('T')[0],
        plan_end_date: addOneMonth(new Date().toISOString().split('T')[0]),

        is_active: true as boolean,
        company_currency: 'XOF',
        company_timezone: 'Africa/Lome',
        mode_paiement: '',
    });

    const handleSubmit = async () => {
        setSubmitError(null);
        setSubmitSuccess(null);

        for (let i = 1; i <= steps.length; i++) {
            if (!validateStep(i)) {
                setCurrentStep(i);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
        }

        const formData = new FormData();

        Object.keys(data).forEach((key) => {
            const value = data[key as keyof typeof data];

            if (key === 'company_logo' && value instanceof File) {
                formData.append(key, value);
            } else if (typeof value === 'boolean') {
                formData.append(key, value ? '1' : '0');
            } else if (value !== null && value !== undefined && value !== '') {
                formData.append(key, String(value));
            }
        });

        try {
            const response = await axios.post('/api/companies', formData, {
                headers: {
                    Accept: 'application/json',
                },
                withCredentials: true,
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / (progressEvent.total || 1));
                    console.log('Upload progress:', percentCompleted + '%');
                },
            });

            const result = response.data;

            if (result.success) {
                setSubmitSuccess(result.message || 'Entreprise créée avec succès !');

                setTimeout(() => {
                    const redirectUrl =
                        '/login?' +
                        'email=' +
                        encodeURIComponent(data.company_email) +
                        '&password=' +
                        encodeURIComponent(data.company_name) +
                        '&redirect_url=/dashboard' +
                        '&redirect_text=' +
                        encodeURIComponent('Veuillez vous connecter avec votre adresse email:');

                    window.location.href = redirectUrl;
                }, 2000);
            } else {
                if (result.errors) {
                    Object.entries(result.errors).forEach(([key, value]) => {
                        const message: string = Array.isArray(value) ? value[0] : value;
                        setError(key as any, message);
                    });
                    setCurrentStep(1);
                } else {
                    setSubmitError(result.message || 'Une erreur est survenue');
                }

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (error) {
            console.error('🔴 Erreur complète:', error);

            if (axios.isAxiosError(error)) {
                if (error.response) {
                    console.error('📊 Status:', error.response.status);
                    console.error('📦 Response Data:', error.response.data);
                    console.error('📋 Headers:', error.response.headers);

                    const result = error.response.data;

                    if (result.errors) {
                        Object.keys(result.errors).forEach((key) => {
                            const errorMessage = Array.isArray(result.errors[key]) ? result.errors[key][0] : result.errors[key];
                            setError(key as any, errorMessage);
                        });
                        setCurrentStep(1);
                    } else {
                        setSubmitError(result.message || `Erreur ${error.response.status}: ${error.response.statusText}`);
                    }
                } else if (error.request) {
                    console.error('📡 Aucune réponse du serveur');
                    console.error('Request:', error.request);
                    setSubmitError('Le serveur ne répond pas. Vérifiez que le backend est démarré.');
                } else {
                    console.error('⚙️ Erreur de configuration:', error.message);
                    setSubmitError('Erreur lors de la préparation de la requête.');
                }
            } else {
                console.error('❌ Erreur inattendue:', error);
                setSubmitError('Une erreur inattendue est survenue. Veuillez réessayer.');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    const handleFileChange = (file: File | null) => {
        if (file) {
            setData('company_logo', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewUrl(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const ALL_STEPS = [
        { id: 1, name: 'Informations de base', icon: Building2, requiredPlans: ['free', 'premium', 'enterprise', 'basic'] },
        { id: 2, name: 'Coordonnées', icon: MapPin, requiredPlans: ['free', 'premium', 'enterprise', 'basic'] },
        { id: 3, name: 'Informations légales', icon: Shield, requiredPlans: ['free', 'premium', 'enterprise', 'basic'] },
        { id: 4, name: 'Configuration', icon: Info, requiredPlans: ['free', 'premium', 'enterprise', 'basic'] },
        { id: 5, name: 'Paiement', icon: GiReceiveMoney, requiredPlans: ['premium', 'enterprise', 'basic'] },
    ];

    const steps = ALL_STEPS.filter((step) => step.requiredPlans.includes(plan)) || ALL_STEPS;

    const handleStepClick = (stepId: number) => {
        if (stepId > currentStep) {
            for (let i = currentStep; i < stepId; i++) {
                if (!validateStep(i)) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
            }
        }
        setCurrentStep(stepId);
    };

    const goBack = () => window.history.back();

    const CURRENCY_OPTIONS = [
        { value: 'XOF', label: 'XOF (Franc CFA)' },
        { value: 'EUR', label: 'EUR (Euro)' },
        { value: 'USD', label: 'USD (Dollar américain)' },
        { value: 'GHS', label: 'GHS (Cedi ghanéen)' },
    ];

    const TIMEZONE_OPTIONS = [
        { value: 'Africa/Lome', label: 'Africa/Lome (Togo)' },
        { value: 'America/New_York', label: 'America/New_York (Etats-Unis)' },
        { value: 'Europe/Paris', label: 'Europe/Paris (France)' },
        { value: 'Asia/Tokyo', label: 'Asia/Tokyo (Japon)' },
    ];

    const regions = [
        { value: 'Togo', label: 'Togo' },
        { value: 'Bénin', label: 'Bénin' },
        { value: 'Burkina Faso', label: 'Burkina Faso' },
        { value: "Côte d'Ivoire", label: "Côte d'Ivoire" },
        { value: 'Guinée', label: 'Guinée' },
    ];

    const validateStep = (step: number): boolean => {
        const newErrors: any = {};

        switch (step) {
            case 1:
                if (!data.company_name.trim()) {
                    newErrors.company_name = "Le nom de l'entreprise est obligatoire";
                }
                if (!data.company_email.trim()) {
                    newErrors.company_email = "L'email de l'entreprise est obligatoire";
                }
                if (!data.company_phone.trim()) {
                    newErrors.company_phone = 'Le téléphone est obligatoire';
                }
                break;

            case 2:
                break;

            case 3:
                break;

            case 4:
                if (!data.plan_start_date) {
                    newErrors.plan_start_date = 'La date de début du plan est obligatoire';
                }
                if (!data.plan_end_date) {
                    newErrors.plan_end_date = 'La date de fin du plan est obligatoire';
                }
                break;

            case 5:
                if (!data.mode_paiement && ['premium', 'enterprise', 'basic'].includes(plan)) {
                    newErrors.mode_paiement = 'Veuillez sélectionner un mode de paiement';
                }
                break;

            default:
                break;
        }

        Object.keys(newErrors).forEach((key) => {
            setError(key as any, newErrors[key]);
        });

        return Object.keys(newErrors).length === 0;
    };

    const handleNext = () => {
        if (validateStep(currentStep)) {
            setCurrentStep(Math.min(steps.length, currentStep + 1));
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    const handlePrevious = () => {
        setCurrentStep(Math.max(1, currentStep - 1));
    };

    return (
        <>
            <Head title="Ajouter une Entreprise" />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 px-4 py-8 transition-colors duration-300 dark:from-black dark:via-gray-950 dark:to-black">
                <div className="relative mx-auto max-w-6xl">
                    <Button className="hidden md:absolute md:block" onClick={goBack}>
                        Retour
                    </Button>

                    <button className="absolute right-0 h-8 w-8 cursor-pointer rounded-full md:hidden" onClick={goBack} aria-label="Retour">
                        <IoIosCloseCircle className="h-full w-full rounded-full bg-white text-red-400" />
                    </button>
                    {/* Header */}
                    <div className="mb-8 text-center">
                        <h1 className="mb-2 text-4xl font-bold text-gray-900 dark:text-white">Créer une nouvelle entreprise</h1>
                        <p className="text-gray-600 dark:text-gray-400">Remplissez les informations pour ajouter votre entreprise</p>
                    </div>

                    {/* Progress Steps */}
                    <div className="mb-8">
                        <div className="flex items-center justify-between">
                            {steps.map((step, index) => (
                                <div key={step.id} className="flex flex-1 items-center">
                                    <div className="flex flex-1 cursor-pointer flex-col items-center" onClick={() => handleStepClick(step.id)}>
                                        <div
                                            className={`flex h-12 w-12 items-center justify-center rounded-full border-2 transition-all ${
                                                currentStep >= step.id
                                                    ? 'border-blue-500 bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg'
                                                    : 'border-gray-300 bg-white text-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-500'
                                            }`}
                                        >
                                            <step.icon className="h-6 w-6" />
                                        </div>
                                        <span
                                            className={`mt-2 hidden text-center text-xs font-medium md:block ${
                                                currentStep >= step.id ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'
                                            }`}
                                        >
                                            {step.name}
                                        </span>
                                    </div>
                                    {index < steps.length - 1 && (
                                        <div
                                            className={`mx-2 h-0.5 flex-1 transition-all ${
                                                currentStep > step.id
                                                    ? 'bg-gradient-to-r from-blue-500 to-indigo-600'
                                                    : 'bg-gray-300 dark:bg-gray-700'
                                            }`}
                                        />
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Formulaire */}
                    <div className="relative rounded-2xl border border-gray-200 bg-white p-8 shadow-xl transition-colors duration-300 dark:border-gray-700 dark:bg-white/15">
                        {/* Message de succès */}
                        {submitSuccess && (
                            <div className="mb-6 rounded-xl border-l-4 border-green-500 bg-green-50 p-4 dark:bg-green-900/20">
                                <div className="flex items-center">
                                    <div className="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40">
                                        <svg className="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fillRule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <p className="font-semibold text-green-800 dark:text-green-400">{submitSuccess}</p>
                                </div>
                            </div>
                        )}

                        {/* Message d'erreur général */}
                        {submitError && (
                            <div className="mb-6 rounded-xl border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20">
                                <div className="flex items-center">
                                    <div className="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40">
                                        <svg className="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fillRule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <p className="font-semibold text-red-800 dark:text-red-400">{submitError}</p>
                                </div>
                            </div>
                        )}

                        <div className="absolute right-8">
                            <span className="text-2xl font-semibold text-blue-600 capitalize dark:text-blue-400">
                                {plan === 'free' ? 'Gratuit' : plan}
                            </span>
                        </div>

                        <div className="space-y-8">
                            {/* Step 1: Informations de base */}
                            {currentStep === 1 && (
                                <div className="space-y-6">
                                    <div className="border-b border-gray-200 pb-4 dark:border-gray-700">
                                        <h2 className="flex items-center text-xl font-bold text-gray-900 dark:text-white">
                                            <Building2 className="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            Informations de base
                                        </h2>
                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Les informations essentielles de votre entreprise
                                        </p>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <TextInput
                                                error={errors.company_name}
                                                label="Nom de l'entreprise"
                                                type="text"
                                                id="company_name"
                                                value={data.company_name}
                                                onChange={(e) => setData('company_name', e.target.value)}
                                                onFocus={() => setError('company_name', '')}
                                                placeholder="Ex: Tech Solutions Inc."
                                                required
                                            />
                                        </div>
                                        <div>
                                            <TextInput
                                                error={errors.company_email}
                                                label="Email de l'entreprise"
                                                type="email"
                                                id="company_email"
                                                value={data.company_email}
                                                onChange={(e) => setData('company_email', e.target.value)}
                                                onFocus={() => setError('company_email', '')}
                                                placeholder="contact@entreprise.com"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <TextInput
                                                error={errors.company_phone}
                                                label="Téléphone de l'entreprise"
                                                type="tel"
                                                id="company_phone"
                                                value={data.company_phone}
                                                onChange={(e) => setData('company_phone', e.target.value)}
                                                onFocus={() => setError('company_phone', '')}
                                                placeholder="+228 XX XX XX XX"
                                                required
                                            />
                                        </div>

                                        <div>
                                            <TextInput
                                                error={errors.company_website}
                                                label="Site web de l'entreprise"
                                                type="url"
                                                id="company_website"
                                                value={data.company_website}
                                                onChange={(e) => setData('company_website', e.target.value)}
                                                placeholder="https://www.entreprise.com"
                                            />
                                        </div>

                                        <div>
                                            <TextArea
                                                error={errors.company_description}
                                                label="Description de l'entreprise"
                                                id="company_description"
                                                value={data.company_description}
                                                onChange={(e) => setData('company_description', e.target.value)}
                                                placeholder="Décrivez votre entreprise, ses activités, sa mission..."
                                                rows={6}
                                            />
                                        </div>

                                        <div>
                                            <FileInput
                                                label="Logo de l'entreprise"
                                                error={errors.company_logo}
                                                id="company_logo"
                                                accept="image/*"
                                                onFileChange={handleFileChange}
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 2: Coordonnées */}
                            {currentStep === 2 && (
                                <div className="space-y-6">
                                    <div className="border-b border-gray-200 pb-4 dark:border-gray-700">
                                        <h2 className="flex items-center text-xl font-bold text-gray-900 dark:text-white">
                                            <MapPin className="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            Coordonnées
                                        </h2>
                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">Adresse et localisation de l'entreprise</p>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="">
                                            <TextInput
                                                label="Adresse complète"
                                                error={errors.company_address}
                                                type="text"
                                                id="company_address"
                                                value={data.company_address}
                                                onChange={(e) => setData('company_address', e.target.value)}
                                                placeholder="123 Rue de l'Indépendance"
                                            />
                                        </div>

                                        <div>
                                            <TextInput
                                                label="Code postal"
                                                type="text"
                                                id="company_postal_code"
                                                value={data.company_postal_code}
                                                onChange={(e) => setData('company_postal_code', e.target.value)}
                                                placeholder="BP 12345"
                                                error={errors.company_postal_code}
                                            />
                                        </div>

                                        <div>
                                            <TextInput
                                                label="Ville"
                                                type="text"
                                                id="company_city"
                                                value={data.company_city}
                                                onChange={(e) => setData('company_city', e.target.value)}
                                                placeholder="Lomé"
                                                error={errors.company_city}
                                            />
                                        </div>

                                        <div>
                                            <SelectInput
                                                id="company_country"
                                                label="Pays"
                                                value={data.company_country}
                                                onChange={(value) => setData('company_country', value)}
                                                options={regions}
                                                error={errors.company_country}
                                                placeholder="Sélectionnez un pays"
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 3: Informations légales */}
                            {currentStep === 3 && (
                                <div className="space-y-6">
                                    <div className="border-b border-gray-200 pb-4 dark:border-gray-700">
                                        <h2 className="flex items-center text-xl font-bold text-gray-900 dark:text-white">
                                            <Shield className="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            Informations légales
                                        </h2>
                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">Documents et informations fiscales</p>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <TextInput
                                                label="Numéro d'enregistrement"
                                                type="text"
                                                id="company_registration_number"
                                                value={data.company_registration_number}
                                                onChange={(e) => setData('company_registration_number', e.target.value)}
                                                placeholder="RCCM-TG-XXX-XXX"
                                                error={errors.company_registration_number}
                                            />
                                        </div>

                                        <div>
                                            <TextInput
                                                label="Numéro fiscal / NIF"
                                                type="text"
                                                id="company_tax_number"
                                                value={data.company_tax_number}
                                                onChange={(e) => setData('company_tax_number', e.target.value)}
                                                placeholder="NIF-XXXXXXXXX"
                                                error={errors.company_tax_number}
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 4: Configuration */}
                            {currentStep === 4 && (
                                <div className="space-y-6">
                                    <div className="border-b border-gray-200 pb-4 dark:border-gray-700">
                                        <h2 className="flex items-center text-xl font-bold text-gray-900 dark:text-white">
                                            <Info className="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            Configuration
                                        </h2>
                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">Paramètres de fonctionnement</p>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <DateInput
                                                id="plan_start_date"
                                                label="Date de début du plan"
                                                value={data.plan_start_date}
                                                onChange={(value) => (
                                                    setData('plan_start_date', value),
                                                    setData('plan_end_date', addOneMonth(value)),
                                                    setError('plan_start_date', '')
                                                )}
                                                error={errors.plan_start_date}
                                                min={new Date().toISOString().split('T')[0]}
                                                required
                                            />
                                        </div>

                                        <div>
                                            <DateInput
                                                id="plan_end_date"
                                                label="Date de fin du plan"
                                                value={data.plan_end_date}
                                                onChange={(value) => (setData('plan_end_date', value), setError('plan_end_date', ''))}
                                                error={errors.plan_end_date}
                                                min={data.plan_start_date}
                                                required
                                            />
                                        </div>

                                        <div>
                                            <SelectInput
                                                id="company_currency"
                                                label="Devise"
                                                value={data.company_currency}
                                                onChange={(value) => setData('company_currency', value)}
                                                options={CURRENCY_OPTIONS}
                                                error={errors.company_currency}
                                                placeholder="Sélectionnez une devise"
                                            />
                                        </div>

                                        <div>
                                            <SelectInput
                                                id="company_timezone"
                                                label="Fuseau horaire"
                                                value={data.company_timezone}
                                                onChange={(value) => setData('company_timezone', value)}
                                                options={TIMEZONE_OPTIONS}
                                                error={errors.company_timezone}
                                                placeholder="Sélectionnez un fuseau horaire"
                                            />
                                        </div>

                                        <div className="md:col-span-2">
                                            <label className="group flex cursor-pointer items-center space-x-3">
                                                <input
                                                    type="checkbox"
                                                    checked={data.is_active}
                                                    onChange={(e) => setData('is_active', e.target.checked)}
                                                    className="h-5 w-5 rounded border-gray-300 text-blue-600 transition-all focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600"
                                                />
                                                <span className="text-sm font-semibold text-gray-700 transition-colors group-hover:text-blue-600 dark:text-gray-300 dark:group-hover:text-blue-400">
                                                    Entreprise active
                                                </span>
                                            </label>
                                            <p className="mt-1 ml-8 text-xs text-gray-500 dark:text-gray-400">
                                                Décochez cette option pour désactiver temporairement l'entreprise
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 5: Paiement */}
                            {currentStep === 5 && (
                                <div className="space-y-6">
                                    <div className="border-b border-gray-200 pb-4 dark:border-gray-700">
                                        <h2 className="flex items-center text-xl font-bold text-gray-900 dark:text-white">
                                            <GiReceiveMoney className="mr-2 h-5 w-5 text-blue-600 dark:text-blue-400" />
                                            Paiement
                                        </h2>
                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">Choisissez votre mode de paiement</p>
                                    </div>
                                    <div className="grid gap-6 sm:grid-cols-2 md:grid-cols-4">
                                        {PAIMENT_OPTIONS.map((el, index) => (
                                            <CardPaiement
                                                key={index}
                                                label={el.label}
                                                icon={el.icon}
                                                selected={data.mode_paiement === el.value}
                                                onSelect={() => {
                                                    setData('mode_paiement', el.value);
                                                    setError('mode_paiement', '');
                                                }}
                                            />
                                        ))}
                                    </div>
                                    {errors.mode_paiement && <p className="text-sm text-red-600 dark:text-red-400">{errors.mode_paiement}</p>}
                                </div>
                            )}

                            {/* Navigation Buttons */}
                            <div className="flex items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700">
                                <button
                                    type="button"
                                    onClick={handlePrevious}
                                    disabled={currentStep === 1}
                                    className="flex cursor-pointer items-center rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition-all hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    <svg className="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Précédent
                                </button>

                                {currentStep < steps.length ? (
                                    <button
                                        type="button"
                                        onClick={handleNext}
                                        className="flex cursor-pointer items-center rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 font-semibold text-white shadow-lg transition-all hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl"
                                    >
                                        Suivant
                                        <svg className="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={handleSubmit}
                                        disabled={processing}
                                        className="flex cursor-pointer items-center rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-3 font-semibold text-white shadow-lg transition-all hover:from-green-700 hover:to-emerald-700 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <svg className="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                        </svg>
                                        {processing ? (
                                            'Enregistrement...'
                                        ) : (
                                            <>
                                                Enregistrer <span className="ml-1 hidden md:block">l' entreprise</span>
                                            </>
                                        )}
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Info Card */}
                    <div className="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-900/20">
                        <div className="flex items-start space-x-3">
                            <div className="flex-shrink-0">
                                <Info className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 className="mb-1 text-sm font-semibold text-blue-900 dark:text-blue-300">Informations importantes</h3>
                                <ul className="space-y-1 text-sm text-blue-800 dark:text-blue-400">
                                    <li>
                                        • Les champs marqués d'un astérisque (<span className="text-red-600">*</span>) sont obligatoires
                                    </li>
                                    <li>• Le logo doit être au format JPG, PNG ou SVG (max 2MB)</li>
                                    <li>• Vous pourrez modifier ces informations plus tard</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
