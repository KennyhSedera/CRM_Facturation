import Head from '@/components/head';
import CompanyCard from '@/components/ui/company-card';
import AppLayout from '@/layouts/app-layout';
import { Company } from '@/types';
import { Search } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

interface BreadcrumbItem {
    title: string;
    href: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Entreprises',
        href: '/companies',
    },
];

export default function CompanyPage() {
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');
    const [companies, setCompanies] = useState<Company[]>([]);

    const fetchCompanies = useCallback(async () => {
        try {
            const response = await fetch('/api/companies');
            const data = await response.json();
            setCompanies(data.data);
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
        }
    }, []);

    useEffect(() => {
        fetchCompanies();
    }, [fetchCompanies]);

    const filteredCompanies = companies.filter((company) => {
        const matchesSearch = company.company_name.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesStatus = filterStatus === 'all' || company.plan_status === filterStatus;
        return matchesSearch && matchesStatus;
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Entreprises" />
            <div className="mb-8 items-center justify-between border-b border-gray-100 md:flex dark:border-gray-500">
                <div className="mb-2 flex items-center justify-between">
                    <div>
                        <h1 className="mb-1 text-3xl font-bold text-slate-800 dark:text-white">Entreprises</h1>
                        <p className="text-slate-600 dark:text-gray-300">Gérez toutes vos entreprises en un seul endroit</p>
                    </div>
                </div>
                <div>
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-slate-400" />
                        <input
                            type="search"
                            placeholder="Rechercher une entreprise..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-auto rounded-xl border border-slate-200 py-2.5 pr-4 pl-10 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none md:w-full"
                        />
                    </div>
                </div>
            </div>

            {/* Liste des entreprises */}
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
                {filteredCompanies.map((company) => (
                    <CompanyCard
                        key={company.company_id}
                        company={company}
                        onView={(id) => console.log('Voir', id)}
                        // onEdit={(id) => console.log('Éditer', id)}
                        onMore={(id) => console.log('Plus', id)}
                    />
                ))}
            </div>
        </AppLayout>
    );
}

// export default CompanyPage;
