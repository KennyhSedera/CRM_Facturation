import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Article } from '@/types/article';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Articles', href: '/article' }];

const ArticlePage = () => {
    const [articles, setArticles] = useState<Article[]>([]);
    const [loading, setLoading] = useState(true);

    const fetchArticles = async () => {
        try {
            const res = await fetch('/api/articles');
            const data = await res.json();
            setArticles(data.data);
            console.log(data.data);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchArticles();
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Articles" />

            <div className="rounded-lg bg-white p-6 text-gray-900 dark:bg-white/15 dark:text-gray-100">
                <h1 className="mb-6 text-2xl font-bold">📦 Gestion des articles</h1>

                {loading && <p className="text-gray-500 dark:text-gray-400">Chargement des articles...</p>}

                {!loading && articles.length === 0 && <p className="text-gray-500 dark:text-gray-400">Aucun article disponible.</p>}

                {!loading && articles.length > 0 && (
                    <div className="overflow-x-auto">
                        <table className="min-w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-black">
                            <thead className="border-b border-gray-200 dark:border-gray-500">
                                <tr>
                                    <th className="px-4 py-2 text-left">Nom</th>
                                    <th className="px-4 py-2 text-left">Référence</th>
                                    <th className="px-4 py-2 text-left">Source</th>
                                    <th className="px-4 py-2 text-right">Prix</th>
                                    <th className="px-4 py-2 text-right">Stock</th>
                                    <th className="px-4 py-2 text-right">TVA</th>
                                </tr>
                            </thead>
                            <tbody>
                                {articles.map((article) => (
                                    <tr
                                        key={article.article_id}
                                        className="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/15"
                                    >
                                        <td className="px-4 py-2 font-medium">{article.article_name}</td>
                                        <td className="px-4 py-2">{article.article_reference}</td>
                                        <td className="px-4 py-2">{article.article_source}</td>
                                        <td className="px-4 py-2 text-right">{Number(article.selling_price).toLocaleString()} FCFA</td>
                                        <td className="px-4 py-2 text-right">{article.quantity_stock + ' ' + article.article_unité}</td>
                                        <td className="px-4 py-2 text-right">{article.article_tva} %</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AppLayout>
    );
};

export default ArticlePage;
