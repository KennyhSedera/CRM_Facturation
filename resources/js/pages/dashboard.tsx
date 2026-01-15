import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { DashboardData, QuickAction } from '@/types/dash';
import { router } from '@inertiajs/react';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    ChevronDown,
    ChevronUp,
    Clock,
    Euro,
    FileCheck,
    FileText,
    Package,
    TrendingDown,
    TrendingUp,
    Users,
} from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const [data, setData] = useState<DashboardData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [period, setPeriod] = useState('30days');

    useEffect(() => {
        const getDataDB = async () => {
            try {
                setLoading(true);
                setError(null);

                const res = await fetch(`/api/dashboard?period=${period}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    credentials: 'include',
                });

                if (!res.ok) {
                    throw new Error(`Erreur HTTP: ${res.status}`);
                }

                const result = await res.json();
                setData(result);
            } catch (err) {
                console.error('Erreur lors du chargement du dashboard:', err);
                setError(err instanceof Error ? err.message : 'Une erreur est survenue');
            } finally {
                setLoading(false);
            }
        };

        getDataDB();
    }, [period]);

    const getIconStat = (staticon: string) => {
        return staticon === 'Euro'
            ? Euro
            : staticon === 'FileText'
              ? FileText
              : staticon === 'Package'
                ? Package
                : staticon === 'CheckCircle2'
                  ? CheckCircle2
                  : staticon === 'Clock'
                    ? Clock
                    : staticon === 'FileCheck'
                      ? FileCheck
                      : Users;
    };

    const quickActions: QuickAction[] = [
        {
            label: 'Gestion facture',
            icon: FileText,
            href: '/quotes',
            colorClasses: {
                icon: 'text-blue-600 dark:text-blue-400',
                hoverBorder: 'hover:border-blue-500',
                hoverBg: 'hover:bg-blue-50 dark:hover:bg-blue-900/20',
            },
        },
        {
            label: 'Gestion article',
            icon: Package,
            href: '/articles',
            colorClasses: {
                icon: 'text-purple-600 dark:text-purple-400',
                hoverBorder: 'hover:border-purple-500',
                hoverBg: 'hover:bg-purple-50 dark:hover:bg-purple-900/20',
            },
        },
        {
            label: 'Gestion client',
            icon: Users,
            href: '/clients',
            colorClasses: {
                icon: 'text-orange-600 dark:text-orange-400',
                hoverBorder: 'hover:border-orange-500',
                hoverBg: 'hover:bg-orange-50 dark:hover:bg-orange-900/20',
            },
        },
        {
            label: 'Voir rapports',
            icon: TrendingUp,
            href: '/',
            colorClasses: {
                icon: 'text-green-600 dark:text-green-400',
                hoverBorder: 'hover:border-green-500',
                hoverBg: 'hover:bg-green-50 dark:hover:bg-green-900/20',
            },
        },
    ];

    const handleActionClick = (action: QuickAction) => {
        if (action.onClick) {
            action.onClick();
        } else if (action.href) {
            router.visit(action.href);
        }
    };

    const ActivityCurrent = () => {
        const [showAllActivities, setShowAllActivities] = useState(false);

        const displayedActivities = showAllActivities ? data?.recentActivities : data?.recentActivities.slice(0, 3);

        const hasMoreActivities = (data?.recentActivities.length ?? 0) > 3;
        const remainingCount = (data?.recentActivities.length ?? 0) - 3;

        return (
            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/20 dark:bg-white/10">
                <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Activités récentes</h2>
                <div className="mt-4 space-y-3">
                    {displayedActivities?.map((activity, i) => (
                        <div
                            key={i}
                            className="rounded-lg border border-slate-100 p-3 transition-colors animate-in fade-in slide-in-from-top-2 hover:bg-slate-50 dark:border-white/20 dark:hover:bg-gray-700/50"
                            style={{ animationDelay: `${i * 50}ms` }}
                        >
                            <div className="flex items-start gap-3">
                                <div
                                    className={`rounded-full p-2 ${
                                        activity.type === 'alert'
                                            ? 'bg-red-100 dark:bg-red-900/30'
                                            : activity.type === 'payment'
                                              ? 'bg-green-100 dark:bg-green-900/30'
                                              : 'bg-blue-100 dark:bg-blue-900/30'
                                    }`}
                                >
                                    {activity.type === 'alert' ? (
                                        <AlertCircle className="h-4 w-4 text-red-600 dark:text-red-400" />
                                    ) : activity.type === 'user' ? (
                                        <Users className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    ) : activity.type === 'payment' ? (
                                        <Euro className="h-4 w-4 text-green-600 dark:text-green-400" />
                                    ) : (
                                        <FileText className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-medium text-slate-900 dark:text-white">{activity.action}</p>
                                    <p className="truncate text-xs text-slate-600 dark:text-slate-300">{activity.client}</p>
                                    <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-300">{activity.time}</p>
                                </div>
                            </div>
                        </div>
                    ))}

                    {hasMoreActivities && (
                        <button
                            onClick={() => setShowAllActivities(!showAllActivities)}
                            className="group flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 p-2.5 text-sm font-medium text-slate-600 transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-white/20 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:bg-blue-900/20 dark:hover:text-blue-400"
                        >
                            {showAllActivities ? (
                                <>
                                    <ChevronUp className="h-4 w-4 transition-transform group-hover:-translate-y-0.5" />
                                    Voir moins
                                </>
                            ) : (
                                <>
                                    <ChevronDown className="h-4 w-4 transition-transform group-hover:translate-y-0.5" />
                                    Voir {remainingCount} activité{remainingCount > 1 ? 's' : ''} de plus
                                </>
                            )}
                        </button>
                    )}
                </div>
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl bg-slate-50 p-6 dark:bg-transparent">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-slate-900 dark:text-white">Tableau de bord</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-300">Vue d'ensemble de votre activité commerciale</p>
                    </div>
                    <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <Calendar className="h-4 w-4" />
                        <span>
                            {new Date().toLocaleDateString('fr-FR', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                            })}
                        </span>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {data?.stats.map((stat, index) => {
                        const Icon = getIconStat(stat.icon);
                        return (
                            <div
                                key={index}
                                className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-white/20 dark:bg-white/10"
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex-1">
                                        <p className="text-sm font-medium text-slate-600 dark:text-slate-300">{stat.title}</p>
                                        <p className="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{stat.value}</p>
                                        <div className="mt-2 flex items-center gap-1">
                                            {stat.changeType === 'positive' ? (
                                                <TrendingUp className="h-4 w-4 text-green-500" />
                                            ) : (
                                                <TrendingDown className="h-4 w-4 text-red-500" />
                                            )}
                                            <span
                                                className={`text-sm font-medium ${
                                                    stat.changeType === 'positive'
                                                        ? 'text-green-600 dark:text-green-400'
                                                        : 'text-red-600 dark:text-red-400'
                                                }`}
                                            >
                                                {stat.change}
                                            </span>
                                            <span className="text-sm text-slate-500 dark:text-slate-300">vs mois dernier</span>
                                        </div>
                                    </div>
                                    <div className={`rounded-full ${stat.bgColor} bg-opacity-10 dark:bg-opacity-20 p-3`}>
                                        <Icon className={`h-6 w-6 text-white`} />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    {data?.invoicesStatus.map((item, index) => {
                        const Icon = getIconStat(item.icon);
                        return (
                            <div
                                key={index}
                                className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/20 dark:bg-white/10"
                            >
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-600 dark:text-slate-300">{item.status}</p>
                                        <p className={`mt-2 text-2xl font-bold ${item.color}`}>{item.count}</p>
                                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-300">{item.amount}</p>
                                    </div>
                                    <div className={`rounded-lg p-3 ${item.bgColor}`}>
                                        <Icon className={`h-6 w-6 ${item.color}`} />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2 dark:border-white/20 dark:bg-white/10">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Évolution du chiffre d'affaires</h2>
                            <select className="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option>7 derniers jours</option>
                                <option>30 derniers jours</option>
                                <option>Ce mois-ci</option>
                                <option>Cette année</option>
                            </select>
                        </div>
                        <div className="mt-4 flex h-64 items-center justify-center text-slate-400">
                            <div className="text-center">
                                <TrendingUp className="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" />
                                <p className="mt-2 text-sm">Graphique à intégrer (Recharts, Chart.js...)</p>
                            </div>
                        </div>
                    </div>

                    <ActivityCurrent />
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {data?.topArticles && data.topArticles.length > 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/20 dark:bg-white/10">
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Articles les plus vendus</h2>
                            <div className="mt-4 space-y-3">
                                {data?.topArticles.map((article, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between rounded-lg border border-slate-100 p-3 dark:border-white/20"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 text-sm font-semibold text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium text-slate-900 dark:text-white">{article.name}</p>
                                                <p className="text-xs text-slate-500 dark:text-slate-300">{article.sales} ventes</p>
                                            </div>
                                        </div>
                                        <p className="text-sm font-semibold text-slate-900 dark:text-white">{article.revenue}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                    {data?.topClients && data.topClients.length > 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/20 dark:bg-white/10">
                            <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Meilleurs clients</h2>
                            <div className="mt-4 space-y-3">
                                {data?.topClients.map((client, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between rounded-lg border border-slate-100 p-3 dark:border-white/20"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-sm font-semibold text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium text-slate-900 dark:text-white">{client.name}</p>
                                                <p className="text-xs text-slate-500 dark:text-slate-300">{client.invoices} factures</p>
                                            </div>
                                        </div>
                                        <p className="text-sm font-semibold text-slate-900 dark:text-white">{client.amount}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/20 dark:bg-white/10">
                    <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Actions rapides</h2>
                    <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        {quickActions.map((action, index) => {
                            const Icon = action.icon;
                            return (
                                <button
                                    key={index}
                                    onClick={() => handleActionClick(action)}
                                    className={`flex cursor-pointer flex-col items-center gap-2 rounded-lg border border-slate-200 p-4 transition-all dark:border-white/20 ${action.colorClasses.hoverBorder} ${action.colorClasses.hoverBg}`}
                                >
                                    <Icon className={`h-6 w-6 ${action.colorClasses.icon}`} />
                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">{action.label}</span>
                                </button>
                            );
                        })}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
