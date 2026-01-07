import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Utilisateurs',
        href: '/users',
    },
];

const userPage = () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Utilisateur" />
            Utilisateur
        </AppLayout>
    );
};

export default userPage;
