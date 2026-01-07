import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mouvement Article',
        href: '/mvt-article',
    },
];

const mvtArticlePage = () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Mouvement Articles" />
            Mouvement Articles
        </AppLayout>
    );
};

export default mvtArticlePage;
