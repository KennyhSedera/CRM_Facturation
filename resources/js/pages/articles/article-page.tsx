import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, PageProps } from '@/types';
import { Article } from '@/types/article';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { FiEdit, FiTrash2 } from 'react-icons/fi';
import { IoSearchOutline } from 'react-icons/io5';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Articles', href: '/article' }];

const ArticlePage = () => {
    const [articles, setArticles] = useState<Article[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [itemsPerPage, setItemsPerPage] = useState(10);

    const { auth } = usePage<PageProps>().props;

    const fetchArticles = async () => {
        try {
            const res = await fetch('/api/articles/company/' + auth?.user?.company_id);
            const { data } = await res.json();
            setArticles(data);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchArticles();
    }, []);

    const filteredArticles = articles.filter(
        (article) =>
            article.article_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            article.article_reference.toLowerCase().includes(searchTerm.toLowerCase()) ||
            article.article_source.toLowerCase().includes(searchTerm.toLowerCase()),
    );

    // Pagination calculations
    const totalPages = Math.ceil(filteredArticles.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentArticles = filteredArticles.slice(startIndex, endIndex);

    // Reset to page 1 when search changes
    useEffect(() => {
        setCurrentPage(1);
    }, [searchTerm]);

    const goToPage = (page: number) => {
        setCurrentPage(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const getPageNumbers = () => {
        const pages = [];
        const maxVisible = 5;

        if (totalPages <= maxVisible) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            if (currentPage <= 3) {
                for (let i = 1; i <= 4; i++) pages.push(i);
                pages.push('...');
                pages.push(totalPages);
            } else if (currentPage >= totalPages - 2) {
                pages.push(1);
                pages.push('...');
                for (let i = totalPages - 3; i <= totalPages; i++) pages.push(i);
            } else {
                pages.push(1);
                pages.push('...');
                pages.push(currentPage - 1);
                pages.push(currentPage);
                pages.push(currentPage + 1);
                pages.push('...');
                pages.push(totalPages);
            }
        }

        return pages;
    };

    const getStockStatus = (quantity: number) => {
        if (quantity === 0) return { text: 'Rupture', color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' };
        if (quantity < 10) return { text: 'Faible', color: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' };
        return { text: 'En stock', color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' };
    };

    const handleEdit = (articleId: number) => {
        console.log('Edit article:', articleId);
        // Ajoutez votre logique d'édition ici
    };

    const handleDelete = (articleId: number) => {
        console.log('Delete article:', articleId);
        // Ajoutez votre logique de suppression ici
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Articles" />

            <div className="space-y-6">
                {/* Header Section */}
                <div className="rounded-xl bg-gray-50 p-6 shadow-sm dark:bg-white/15">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">📦 Gestion des articles</h1>
                            <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">Gérez votre inventaire d'articles et produits</p>
                        </div>
                        <button className="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-md transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none">
                            + Nouvel article
                        </button>
                    </div>
                    <div className="pt-6">
                        <div className="flex flex-col items-start gap-4 lg:flex-row lg:justify-between">
                            <div className="flex-1">
                                <div className="relative">
                                    <IoSearchOutline className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400" />
                                    <input
                                        type="text"
                                        placeholder="Rechercher par nom, référence ou source..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="w-full rounded-lg border border-gray-300 bg-white py-3 pr-4 pl-10 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-black dark:text-gray-100 dark:placeholder-gray-400"
                                    />
                                    {searchTerm && (
                                        <button
                                            onClick={() => setSearchTerm('')}
                                            className="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    )}
                                </div>
                                {searchTerm && (
                                    <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        {filteredArticles.length} résultat{filteredArticles.length > 1 ? 's' : ''} trouvé
                                        {filteredArticles.length > 1 ? 's' : ''}
                                    </p>
                                )}
                            </div>

                            {!loading && filteredArticles.length > 0 && (
                                <div className="mt-2 flex items-center gap-2 lg:ml-4">
                                    <label className="text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">Articles par page:</label>
                                    <select
                                        value={itemsPerPage}
                                        onChange={(e) => {
                                            setItemsPerPage(Number(e.target.value));
                                            setCurrentPage(1);
                                        }}
                                        className="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-black dark:text-gray-100"
                                    >
                                        <option className="dark:bg-black" value={5}>
                                            5
                                        </option>
                                        <option className="dark:bg-black" value={10}>
                                            10
                                        </option>
                                        <option className="dark:bg-black" value={25}>
                                            25
                                        </option>
                                        <option className="dark:bg-black" value={50}>
                                            50
                                        </option>
                                        <option className="dark:bg-black" value={100}>
                                            100
                                        </option>
                                    </select>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Content Section */}
                <div className="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-white/15">
                    {loading && (
                        <div className="flex items-center justify-center py-16">
                            <div className="text-center">
                                <div className="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-blue-600 dark:border-gray-600 dark:border-t-blue-400"></div>
                                <p className="text-gray-600 dark:text-gray-400">Chargement des articles...</p>
                            </div>
                        </div>
                    )}

                    {!loading && articles.length === 0 && (
                        <div className="py-16 text-center">
                            <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <svg className="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Aucun article disponible</h3>
                            <p className="text-gray-600 dark:text-gray-400">Commencez par ajouter votre premier article</p>
                        </div>
                    )}

                    {!loading && articles.length > 0 && filteredArticles.length === 0 && (
                        <div className="py-16 text-center">
                            <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <svg className="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Aucun résultat trouvé</h3>
                            <p className="text-gray-600 dark:text-gray-400">Essayez avec d'autres mots-clés</p>
                        </div>
                    )}

                    {!loading && filteredArticles.length > 0 && (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-black/20">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            Article
                                        </th>
                                        <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            Référence
                                        </th>
                                        <th className="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            Source
                                        </th>
                                        <th className="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            Prix unitaire
                                        </th>
                                        <th className="px-6 py-4 text-center text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            Stock
                                        </th>
                                        <th className="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            TVA
                                        </th>
                                        <th className="px-6 py-4 text-center text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-300">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {currentArticles.map((article) => {
                                        const stockStatus = getStockStatus(article.quantity_stock);
                                        return (
                                            <tr
                                                key={article.article_id}
                                                className="bg-gray-50 transition hover:bg-white dark:bg-transparent dark:hover:bg-black"
                                            >
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center">
                                                        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                                            <span className="text-lg">📦</span>
                                                        </div>
                                                        <div className="ml-4 font-semibold text-gray-900 dark:text-gray-100">
                                                            {article.article_name}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        {article.article_reference}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{article.article_source}</td>
                                                <td className="px-6 py-4 text-right font-semibold text-gray-900 dark:text-gray-100">
                                                    {Number(article.selling_price).toLocaleString()} FCFA
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex flex-col items-center gap-1">
                                                        <span className="font-semibold text-gray-900 dark:text-gray-100">
                                                            {article.quantity_stock} {article.article_unité}
                                                        </span>
                                                        <span
                                                            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${stockStatus.color}`}
                                                        >
                                                            {stockStatus.text}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">
                                                    {article.article_tva} %
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center justify-center gap-2">
                                                        <button
                                                            onClick={() => handleEdit(article.article_id)}
                                                            className="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                            title="Modifier"
                                                        >
                                                            <FiEdit className="h-4 w-4" />
                                                        </button>
                                                        <button
                                                            onClick={() => handleDelete(article.article_id)}
                                                            className="rounded-lg p-2 text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                            title="Supprimer"
                                                        >
                                                            <FiTrash2 className="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Pagination */}
                    {!loading && filteredArticles.length > 0 && (
                        <div className="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-black/20">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                {/* Results info */}
                                <div className="text-sm text-gray-700 dark:text-gray-300">
                                    Affichage de <span className="font-semibold">{startIndex + 1}</span> à{' '}
                                    <span className="font-semibold">{Math.min(endIndex, filteredArticles.length)}</span> sur{' '}
                                    <span className="font-semibold">{filteredArticles.length}</span> résultats
                                </div>

                                {/* Pagination controls */}
                                <div className="flex items-center gap-2">
                                    {/* Previous button */}
                                    <button
                                        onClick={() => goToPage(currentPage - 1)}
                                        disabled={currentPage === 1}
                                        className="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                    >
                                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>

                                    {/* Page numbers */}
                                    <div className="flex items-center gap-1">
                                        {getPageNumbers().map((page, index) =>
                                            page === '...' ? (
                                                <span key={`ellipsis-${index}`} className="px-3 py-2 text-gray-500 dark:text-gray-400">
                                                    ...
                                                </span>
                                            ) : (
                                                <button
                                                    key={page}
                                                    onClick={() => goToPage(page as number)}
                                                    className={`rounded-lg px-4 py-2 text-sm font-medium transition ${
                                                        currentPage === page
                                                            ? 'bg-blue-600 text-white dark:bg-blue-500'
                                                            : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                                    }`}
                                                >
                                                    {page}
                                                </button>
                                            ),
                                        )}
                                    </div>

                                    {/* Next button */}
                                    <button
                                        onClick={() => goToPage(currentPage + 1)}
                                        disabled={currentPage === totalPages}
                                        className="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                    >
                                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
};

export default ArticlePage;
