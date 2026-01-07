import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients',
    },
];

const cataloguePage = () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clients" />
            cataloguePage
        </AppLayout>
    );
};

export default cataloguePage;
