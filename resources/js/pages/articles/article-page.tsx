import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Articles',
        href: '/article',
    },
];

const articlePage = () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Articles" />
            Articles
        </AppLayout>
    );
};

export default articlePage;
