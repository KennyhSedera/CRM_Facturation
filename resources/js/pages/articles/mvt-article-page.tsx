import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { ArrowDownCircle, ArrowUpCircle, Calendar, ChevronLeft, ChevronRight, Package, Plus, Search, User } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Article {
    article_id: number;
    article_name: string;
    article_reference: string;
    article_source: string;
    article_tva: string;
    article_unité: string;
    company_id: number;
    created_at: string;
    quantity_stock: number;
    selling_price: string;
    updated_at: string;
    user_id: number;
}

interface UserInfo {
    id: number;
    name: string;
    email: string;
    telegram_id: number;
    user_role: string;
    avatar: string | null;
    company_id: number;
}

interface Mouvement {
    mvt_id: number;
    article_id: number;
    user_id: number;
    mvtType: 'entree' | 'sortie';
    mvt_quantity: number;
    mvt_date: string;
    created_at: string;
    updated_at: string;
    article: Article;
    user: UserInfo;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mouvement Article',
        href: '/mvt-article',
    },
];

const mvtArticlePage = () => {
    const [mouvements, setMouvements] = useState<Mouvement[]>([]);
    const [filteredMouvements, setFilteredMouvements] = useState<Mouvement[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [filterType, setFilterType] = useState<string>('all');
    const [currentPage, setCurrentPage] = useState<number>(1);
    const itemsPerPage = 5;

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await fetch('/api/mvt-articles');
                const { data } = await response.json();
                console.log('Mouvement Articles Data:', data);
                setMouvements(data || []);
                setFilteredMouvements(data || []);
                setLoading(false);
            } catch (error) {
                console.error('Error fetching mouvement articles:', error);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    const statsCards = [
        {
            label: 'Tous',
            value: mouvements.length,
            filterType: 'all',
            icon: Package,
            colorClasses: {
                text: 'text-slate-800 dark:text-slate-100',
                bg: 'bg-blue-100 dark:bg-blue-900/30',
                icon: 'text-blue-600 dark:text-blue-400',
            },
        },
        {
            label: 'Entrées',
            value: mouvements.filter((m) => m.mvtType === 'entree').length,
            filterType: 'entree',
            icon: ArrowUpCircle,
            colorClasses: {
                text: 'text-green-600 dark:text-green-400',
                bg: 'bg-green-100 dark:bg-green-900/30',
                icon: 'text-green-600 dark:text-green-400',
            },
        },
        {
            label: 'Sorties',
            value: mouvements.filter((m) => m.mvtType === 'sortie').length,
            filterType: 'sortie',
            icon: ArrowDownCircle,
            colorClasses: {
                text: 'text-red-600 dark:text-red-400',
                bg: 'bg-red-100 dark:bg-red-900/30',
                icon: 'text-red-600 dark:text-red-400',
            },
        },
    ];

    useEffect(() => {
        let filtered = mouvements;

        // Filter by type
        if (filterType !== 'all') {
            filtered = filtered.filter((m) => m.mvtType === filterType);
        }

        // Filter by search term
        if (searchTerm) {
            filtered = filtered.filter(
                (m) =>
                    m.article?.article_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    m.article?.article_reference.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    m.user?.name.toLowerCase().includes(searchTerm.toLowerCase()),
            );
        }

        setFilteredMouvements(filtered);
        setCurrentPage(1);
    }, [searchTerm, filterType, mouvements]);

    const formatDate = (dateString: string): string => {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        });
    };

    // Pagination
    const totalPages = Math.ceil(filteredMouvements.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentMouvements = filteredMouvements.slice(startIndex, endIndex);

    const goToNextPage = () => {
        if (currentPage < totalPages) {
            setCurrentPage(currentPage + 1);
        }
    };

    const goToPrevPage = () => {
        if (currentPage > 1) {
            setCurrentPage(currentPage - 1);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Mouvement Articles" />

            {loading ? (
                <div className="flex h-64 items-center justify-center">
                    <div className="h-12 w-12 animate-spin rounded-full border-b-2 border-blue-500 dark:border-blue-400"></div>
                </div>
            ) : (
                <div className="space-y-6">
                    <div>
                        <div className="grid grid-cols-1 rounded-t-lg bg-gray-50 px-4 pt-4 lg:grid-cols-2 dark:bg-white/10">
                            <div className="">
                                <h1 className="mb-2 text-3xl font-bold text-slate-800 dark:text-slate-100">📦 Mouvement Articles</h1>
                                <p className="text-slate-600 dark:text-slate-400">Gestion des entrées et sorties de stock</p>
                            </div>

                            <div className="grid grid-cols-1 gap-2 md:grid-cols-3">
                                {statsCards.map((card) => {
                                    const Icon = card.icon;
                                    return (
                                        <div
                                            key={card.filterType}
                                            className="cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-2 shadow-sm dark:border-gray-500/50 dark:bg-black"
                                            onClick={() => setFilterType(card.filterType)}
                                        >
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="mb-1 text-sm text-slate-600 dark:text-slate-400">{card.label}</p>
                                                    <p className={`text-3xl font-bold ${card.colorClasses.text}`}>{card.value}</p>
                                                </div>
                                                <div className={`rounded-lg p-3 ${card.colorClasses.bg}`}>
                                                    <Icon className={`h-8 w-8 ${card.colorClasses.icon}`} />
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="rounded-b-lg bg-gray-50 p-4 dark:bg-white/10">
                            <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div className="flex flex-1 items-center gap-2">
                                    <div className="relative max-w-md flex-1">
                                        <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <input
                                            type="text"
                                            placeholder="Rechercher un article, référence ou utilisateur..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="w-full rounded-lg border border-slate-200 bg-white py-2 pr-4 pl-10 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-500/50 dark:bg-black dark:text-slate-100"
                                        />
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <button className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                                        <Plus className="h-4 w-4" />
                                        Nouveau
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {filteredMouvements.length > 0 && (
                        <div className="overflow-hidden rounded-xl border border-slate-200 bg-gray-50 shadow-sm dark:border-gray-500/50 dark:bg-white/10">
                            <div className="border-b border-slate-200 p-4 dark:border-gray-500/50">
                                <div className="flex items-center justify-between">
                                    <h2 className="text-xl font-semibold text-slate-800 dark:text-slate-100">Liste des Mouvements</h2>
                                    <p className="text-sm text-slate-500 dark:text-slate-400">
                                        {filteredMouvements.length} résultat{filteredMouvements.length > 1 ? 's' : ''}
                                    </p>
                                </div>
                            </div>

                            <div className="divide-y divide-slate-200 dark:divide-gray-500/50">
                                {currentMouvements.map((mvt) => (
                                    <div key={mvt.mvt_id} className="p-6 transition-colors duration-150 hover:bg-white dark:hover:bg-black">
                                        <div className="flex items-start justify-between">
                                            <div className="flex flex-1 items-start space-x-4">
                                                <div
                                                    className={`flex-shrink-0 rounded-lg p-3 ${
                                                        mvt.mvtType === 'entree'
                                                            ? 'bg-green-100 dark:bg-green-900/30'
                                                            : 'bg-red-100 dark:bg-red-900/30'
                                                    }`}
                                                >
                                                    {mvt.mvtType === 'entree' ? (
                                                        <ArrowUpCircle className="h-6 w-6 text-green-600 dark:text-green-400" />
                                                    ) : (
                                                        <ArrowDownCircle className="h-6 w-6 text-red-600 dark:text-red-400" />
                                                    )}
                                                </div>

                                                <div className="flex-1">
                                                    <div className="mb-1 flex items-center space-x-3">
                                                        <span
                                                            className={`rounded-full px-3 py-1 text-sm font-medium ${
                                                                mvt.mvtType === 'entree'
                                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                                    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                                            }`}
                                                        >
                                                            {mvt.mvtType === 'entree' ? '↑ Entrée' : '↓ Sortie'}
                                                        </span>
                                                        <span className="text-2xl font-bold text-slate-800 dark:text-slate-100">
                                                            {mvt.mvt_quantity}
                                                        </span>
                                                        <span className="text-slate-500 dark:text-slate-400">
                                                            {mvt.article?.article_unité || 'unités'}
                                                        </span>
                                                    </div>

                                                    {mvt.article && (
                                                        <div className="">
                                                            <h3 className="flex items-center text-sm font-semibold text-slate-800 dark:text-slate-100">
                                                                <Package className="mr-2 h-4 w-4 text-blue-600 dark:text-blue-400" />
                                                                {mvt.article.article_name}
                                                            </h3>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="ml-4 flex-shrink-0 text-right">
                                                <div className="flex items-center justify-end text-sm text-slate-500 dark:text-slate-400">
                                                    <Calendar className="mr-1 h-4 w-4" />
                                                    {formatDate(mvt.mvt_date)}
                                                </div>

                                                {mvt.user && (
                                                    <div className="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                                                        <User className="h-4 w-4 text-slate-400 dark:text-slate-500" />
                                                        <span className="font-medium">{mvt.user.name}</span>
                                                        <span className="hidden text-slate-400 lg:block dark:text-slate-500">•</span>
                                                        <span className="hidden rounded-full bg-purple-100 px-2 py-1 text-xs text-purple-700 lg:block dark:bg-purple-900/30 dark:text-purple-300">
                                                            {mvt.user.user_role}
                                                        </span>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Pagination */}
                            {totalPages > 1 && (
                                <div className="border-t border-slate-200 bg-slate-50 px-6 py-4 dark:border-gray-500/50 dark:bg-black">
                                    <div className="flex items-center justify-between">
                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                            Affichage de {startIndex + 1} à {Math.min(endIndex, filteredMouvements.length)} sur{' '}
                                            {filteredMouvements.length} mouvement{filteredMouvements.length > 1 ? 's' : ''}
                                        </p>
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={goToPrevPage}
                                                disabled={currentPage === 1}
                                                className="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-500/50 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                            >
                                                <ChevronLeft className="h-4 w-4" />
                                                Précédent
                                            </button>
                                            <span className="text-sm text-slate-600 dark:text-slate-400">
                                                Page {currentPage} sur {totalPages}
                                            </span>
                                            <button
                                                onClick={goToNextPage}
                                                disabled={currentPage === totalPages}
                                                className="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-500/50 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                            >
                                                Suivant
                                                <ChevronRight className="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {filteredMouvements.length === 0 && (
                        <div className="py-12 text-center">
                            <Package className="mx-auto mb-4 h-16 w-16 text-slate-300 dark:text-slate-600" />
                            <p className="text-lg text-slate-500 dark:text-slate-400">
                                {searchTerm || filterType !== 'all' ? 'Aucun résultat trouvé' : 'Aucun mouvement trouvé'}
                            </p>
                        </div>
                    )}
                </div>
            )}
        </AppLayout>
    );
};

export default mvtArticlePage;
