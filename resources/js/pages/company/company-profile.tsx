import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { Building2, Calendar, Clock, CreditCard, Edit, Globe, Mail, MapPin, Phone, Shield, TrendingUp } from 'lucide-react';

const CompanyProfile = () => {
    const { auth } = usePage<PageProps>().props;
    const company = auth?.user?.company;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: company?.company_name || 'Company',
            href: '/companies',
        },
    ];

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const calculateDaysRemaining = (endDate: string) => {
        const end = new Date(endDate);
        const today = new Date();
        const diffTime = end.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };

    if (!company) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title={'Profile'} />
                <div className="mx-auto max-w-7xl p-6">
                    <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                            <Building2 className="h-8 w-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 className="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Aucune compagnie trouvée</h3>
                        <p className="text-gray-500 dark:text-gray-400">Aucune information de compagnie disponible.</p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    const daysRemaining = calculateDaysRemaining(company.plan_end_date || '');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={'Profile'} />

            <div className="mx-auto max-w-7xl space-y-6 p-6">
                {/* Hero Section */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700 p-8 text-white shadow-xl dark:from-blue-700 dark:via-blue-800 dark:to-purple-900">
                    <div className="absolute top-0 right-0 -mt-4 -mr-4 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
                    <div className="absolute bottom-0 left-0 -mb-4 -ml-4 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

                    <div className="relative flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                        <div className="flex items-center gap-6">
                            {company.company_logo ? (
                                <img
                                    src={`/storage/${company.company_logo}`}
                                    alt={`${company.company_name} logo`}
                                    className="h-28 w-28 rounded-2xl bg-white object-cover p-1 shadow-2xl dark:bg-gray-800"
                                />
                            ) : (
                                <div className="flex h-28 w-28 items-center justify-center rounded-2xl bg-white/20 text-4xl font-bold shadow-2xl ring-4 ring-white/20 backdrop-blur-sm">
                                    {company.company_name?.charAt(0) || 'C'}
                                </div>
                            )}
                            <div>
                                <h1 className="mb-3 text-4xl font-bold">{company.company_name}</h1>
                                <div className="flex flex-wrap items-center gap-2">
                                    <span
                                        className={`rounded-full px-4 py-1.5 text-sm font-semibold backdrop-blur-sm ${
                                            company.is_active
                                                ? 'bg-green-500/20 text-green-100 ring-1 ring-green-400/30'
                                                : 'bg-red-500/20 text-red-100 ring-1 ring-red-400/30'
                                        }`}
                                    >
                                        {company.is_active ? '● Actif' : '● Inactif'}
                                    </span>
                                    <span className="rounded-full bg-white/20 px-4 py-1.5 text-sm font-semibold capitalize ring-1 ring-white/30 backdrop-blur-sm">
                                        {company.plan_status === 'free' ? '⭐ Gratuit' : `💎 ${company.plan_status}`}
                                    </span>
                                    {daysRemaining > 0 && (
                                        <span className="rounded-full bg-amber-500/20 px-4 py-1.5 text-sm font-semibold ring-1 ring-amber-400/30 backdrop-blur-sm">
                                            {daysRemaining} jours restants
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                        <button className="flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-semibold text-blue-600 shadow-lg transition-all hover:scale-105 hover:shadow-xl dark:bg-gray-800 dark:text-blue-400">
                            <Edit className="h-4 w-4" />
                            Modifier
                        </button>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Contact Card */}
                    <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2 dark:border-gray-700 dark:bg-gray-800">
                        <div className="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                            <h2 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                                <div className="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                                    <Building2 className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                Informations de contact
                            </h2>
                        </div>
                        <div className="space-y-4 p-6">
                            <div className="flex items-center gap-4 rounded-xl border border-blue-100 bg-gradient-to-r from-blue-50 to-transparent p-4 transition-colors hover:border-blue-200 dark:border-blue-900/50 dark:from-blue-900/20 dark:to-transparent dark:hover:border-blue-800">
                                <div className="rounded-xl bg-blue-100 p-3 dark:bg-blue-900/50">
                                    <Mail className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Email</p>
                                    <p className="text-base font-medium break-all text-gray-900 dark:text-white">{company.company_email}</p>
                                </div>
                            </div>

                            <div className="flex items-center gap-4 rounded-xl border border-green-100 bg-gradient-to-r from-green-50 to-transparent p-4 transition-colors hover:border-green-200 dark:border-green-900/50 dark:from-green-900/20 dark:to-transparent dark:hover:border-green-800">
                                <div className="rounded-xl bg-green-100 p-3 dark:bg-green-900/50">
                                    <Phone className="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Téléphone</p>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">{company?.company_phone || 'Non renseigné'}</p>
                                </div>
                            </div>

                            <div className="flex items-center gap-4 rounded-xl border border-purple-100 bg-gradient-to-r from-purple-50 to-transparent p-4 transition-colors hover:border-purple-200 dark:border-purple-900/50 dark:from-purple-900/20 dark:to-transparent dark:hover:border-purple-800">
                                <div className="rounded-xl bg-purple-100 p-3 dark:bg-purple-900/50">
                                    <Globe className="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Site web</p>
                                    <a
                                        href={company?.company_website}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-base font-medium break-all text-purple-600 hover:text-purple-700 hover:underline dark:text-purple-400 dark:hover:text-purple-300"
                                    >
                                        {company?.company_website || 'Non renseigné'}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Stats Card */}
                    <div className="space-y-6">
                        <div className="rounded-2xl border border-gray-200 bg-gradient-to-br from-blue-50 to-white p-6 shadow-sm dark:border-gray-700 dark:from-blue-900/20 dark:to-gray-800">
                            <div className="mb-4 flex items-center justify-between">
                                <div className="rounded-xl bg-blue-100 p-3 dark:bg-blue-900/50">
                                    <TrendingUp className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                                    ACTIF
                                </span>
                            </div>
                            <h3 className="mb-1 text-2xl font-bold text-gray-900 capitalize dark:text-white">
                                {company.plan_status === 'free' ? '⭐ Gratuit' : `💎 ${company.plan_status}`}
                            </h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">Plan actuel</p>
                        </div>

                        <div className="rounded-2xl border border-gray-200 bg-gradient-to-br from-purple-50 to-white p-6 shadow-sm dark:border-gray-700 dark:from-purple-900/20 dark:to-gray-800">
                            <div className="mb-4 flex items-center justify-between">
                                <div className="rounded-xl bg-purple-100 p-3 dark:bg-purple-900/50">
                                    <Shield className="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                </div>
                                <span className="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-600 dark:bg-purple-900/50 dark:text-purple-400">
                                    SÉCURISÉ
                                </span>
                            </div>
                            <h3 className="mb-1 text-2xl font-bold text-gray-900 dark:text-white">{daysRemaining}j</h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400">Jours restants</p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* Location */}
                    <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                            <h2 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                                <div className="rounded-lg bg-red-100 p-2 dark:bg-red-900/50">
                                    <MapPin className="h-5 w-5 text-red-600 dark:text-red-400" />
                                </div>
                                Localisation
                            </h2>
                        </div>
                        <div className="space-y-4 p-6">
                            <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/50">
                                <p className="mb-1 text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Adresse complète</p>
                                <p className="text-base font-medium text-gray-900 dark:text-white">{company.company_address || 'Non renseigné'}</p>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/50">
                                    <p className="mb-1 text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Ville</p>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">{company.company_city}</p>
                                </div>
                                <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/50">
                                    <p className="mb-1 text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Code postal</p>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">{company.company_postal_code}</p>
                                </div>
                            </div>
                            <div className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/50">
                                <p className="mb-1 text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Pays</p>
                                <p className="text-base font-medium text-gray-900 dark:text-white">{company.company_country}</p>
                            </div>
                        </div>
                    </div>

                    {/* Settings */}
                    <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                            <h2 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                                <div className="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/50">
                                    <CreditCard className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                Paramètres
                            </h2>
                        </div>
                        <div className="space-y-4 p-6">
                            <div className="flex items-center gap-4 rounded-xl border border-amber-100 bg-gradient-to-r from-amber-50 to-transparent p-4 dark:border-amber-900/50 dark:from-amber-900/20 dark:to-transparent">
                                <div className="rounded-xl bg-amber-100 p-3 dark:bg-amber-900/50">
                                    <CreditCard className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Devise</p>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">
                                        {company.company_currency || 'Non renseigné'}
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-4 rounded-xl border border-indigo-100 bg-gradient-to-r from-indigo-50 to-transparent p-4 dark:border-indigo-900/50 dark:from-indigo-900/20 dark:to-transparent">
                                <div className="rounded-xl bg-indigo-100 p-3 dark:bg-indigo-900/50">
                                    <Clock className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Fuseau horaire</p>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">
                                        {company.company_timezone || 'Non renseigné'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Subscription Timeline */}
                <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div className="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
                        <h2 className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <div className="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                                <Calendar className="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            Période d'abonnement
                        </h2>
                    </div>
                    <div className="p-6">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <div className="mb-2 flex items-center gap-4">
                                    <div className="rounded-xl bg-green-100 p-3 dark:bg-green-900/50">
                                        <Calendar className="h-5 w-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Date de début</p>
                                        <p className="text-base font-semibold text-gray-900 dark:text-white">
                                            {formatDate(company.plan_start_date || '')}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="flex-1 px-8">
                                <div className="relative">
                                    <div className="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div
                                            className="h-full rounded-full bg-gradient-to-r from-green-500 to-blue-500 transition-all duration-500"
                                            style={{ width: `${Math.max(10, 100 - (daysRemaining / 365) * 100)}%` }}
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div className="flex-1 text-right">
                                <div className="mb-2 flex items-center justify-end gap-4">
                                    <div>
                                        <p className="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">Date de fin</p>
                                        <p className="text-base font-semibold text-gray-900 dark:text-white">
                                            {formatDate(company.plan_end_date || '')}
                                        </p>
                                    </div>
                                    <div className="rounded-xl bg-blue-100 p-3 dark:bg-blue-900/50">
                                        <Calendar className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default CompanyProfile;
