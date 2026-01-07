import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reçus / Factures',
        href: '/quotes',
    },
];

const quotePage = () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Réçus" />
            Réçus
        </AppLayout>
    );
};

export default quotePage;
