import Head from '@/components/head';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, PageProps } from '@/types';
import { ApiWrapper, Client, PaginationData } from '@/types/client';
import { usePage } from '@inertiajs/react';
import axios from 'axios';
import { ChevronLeft, ChevronRight, Edit, Eye, Filter, Mail, MapPin, Phone, Plus, Search, Trash2 } from 'lucide-react';
import React, { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients',
    },
];

const ClientPage: React.FC = () => {
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [statusFilter, setStatusFilter] = useState<string>('all');
    const [clients, setClients] = useState<Client[]>([]);
    const { auth } = usePage<PageProps>().props;
    const [pagination, setPagination] = useState<PaginationData>({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0,
    });
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        const fetchClients = async () => {
            setLoading(true);
            try {
                const response = await axios.get('/api/clients/company/' + auth.user.company_id);
                const result: ApiWrapper = response.data;

                if (result.success && result.data) {
                    const data = result.data;
                    setClients(data.data);
                    setPagination({
                        current_page: data.current_page,
                        last_page: data.last_page,
                        per_page: data.per_page,
                        total: data.total,
                        from: data.from,
                        to: data.to,
                    });
                } else {
                    console.error('Erreur API:', result.message);
                    setClients([]);
                }
            } catch (error) {
                if (axios.isAxiosError(error)) {
                    console.error('Erreur API:', error.response?.data?.message || error.message);
                    // La redirection 401 est gérée par l'intercepteur
                } else {
                    console.error('Erreur lors du chargement:', error);
                }
                setClients([]);
            } finally {
                setLoading(false);
            }
        };

        fetchClients();
    }, []);

    const filteredClients = Array.isArray(clients)
        ? clients.filter((client) => {
              const matchesSearch =
                  client.client_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                  client.client_email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                  client.client_reference.toLowerCase().includes(searchTerm.toLowerCase());
              const matchesStatus = statusFilter === 'all' || client.client_status === statusFilter;
              return matchesSearch && matchesStatus;
          })
        : [];

    const getStatusBadge = (status: Client['client_status']): string => {
        const styles: Record<Client['client_status'], string> = {
            active: 'bg-green-100 text-green-700 border-green-200',
            inactive: 'bg-gray-100 text-gray-700 border-gray-200',
            pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        };
        return styles[status];
    };

    const getStatusLabel = (status: Client['client_status']): string => {
        const labels: Record<Client['client_status'], string> = {
            active: 'Actif',
            inactive: 'Inactif',
            pending: 'En attente',
        };
        return labels[status];
    };

    const activeClientsCount = Array.isArray(clients) ? clients.filter((c) => c.client_status === 'active').length : 0;
    const vipClientsCount = Array.isArray(clients) ? clients.filter((c) => c.client_note?.toLowerCase().includes('vip')).length : 0;

    const handlePageChange = async (page: number): Promise<void> => {
        setLoading(true);
        try {
            const response = await fetch(`/api/clients?page=${page}`);
            const result: ApiWrapper = await response.json();

            if (result.success && result.data) {
                const data = result.data;
                setClients(data.data);
                setPagination({
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total,
                    from: data.from,
                    to: data.to,
                });
            } else {
                console.error('Erreur API:', result.message);
                setClients([]);
            }
        } catch (error) {
            console.error('Erreur lors du changement de page:', error);
            setClients([]);
        } finally {
            setLoading(false);
        }
    };

    const stats = [
        { label: 'Total Clients', value: pagination.total, trend: '↑ 12% ce mois', trendColor: 'text-green-600' },
        { label: 'Clients Actifs', value: activeClientsCount },
        { label: 'Clients VIP', value: vipClientsCount },
        { label: 'Nouveaux Clients (30j)', value: clients.length },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clients" />

            <div className="min-h-screen rounded-2xl bg-gray-50 p-6 dark:bg-white/15 dark:text-white">
                {/* Header */}
                <div className="mb-6">
                    <div className="mb-2 flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Clients</h1>
                            <p className="mt-1 text-gray-600 dark:text-gray-300">Gérez vos clients et leurs informations</p>
                        </div>
                        <button className="flex cursor-pointer items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                            <Plus className="h-4 w-4" />
                            Nouveau Client
                        </button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                    {stats.map((el, i) => (
                        <div key={i} className="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-500 dark:bg-black">
                            <div className="mb-1 text-sm text-gray-600 dark:text-gray-300">{el.label}</div>
                            <div className="text-2xl font-bold text-gray-900 dark:text-gray-200">{el.value}</div>
                            {el.trend && <div className={`mt-1 text-xs ${el.trendColor}`}>{el.trend}</div>}
                        </div>
                    ))}
                </div>

                {/* Filters and Search */}
                <div className="mb-6 rounded-lg border border-gray-200 bg-white dark:border-gray-500 dark:bg-black">
                    <div className="flex flex-col gap-4 p-4 sm:flex-row">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="search"
                                placeholder="Rechercher par nom, email ou référence..."
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-500"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>
                        <div className="flex gap-2">
                            <select
                                className="cursor-pointer rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-gray-500"
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                            >
                                <option className="dark:bg-black" value="all">
                                    Tous les statuts
                                </option>
                                <option className="dark:bg-black" value="active">
                                    Actif
                                </option>
                                <option className="dark:bg-black" value="inactive">
                                    Inactif
                                </option>
                                <option className="dark:bg-black" value="pending">
                                    En attente
                                </option>
                            </select>
                            <button className="flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50 dark:border-gray-500 dark:bg-black dark:hover:bg-gray-900">
                                <Filter className="h-4 w-4" />
                                Filtres
                            </button>
                        </div>
                    </div>
                </div>

                {/* Clients Table */}
                <div className="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-500 dark:bg-black">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="border-b border-gray-200 bg-gray-50 dark:border-gray-500 dark:bg-black">
                                <tr>
                                    <th className="px-10 py-3 text-left text-xs font-medium tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Client
                                    </th>
                                    <th className="px-10 py-3 text-left text-xs font-medium tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Contact
                                    </th>
                                    <th className="px-10 py-3 text-left text-xs font-medium tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Localisation
                                    </th>
                                    <th className="px-10 py-3 text-left text-xs font-medium tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Statut
                                    </th>
                                    <th className="px-10 py-3 text-right text-xs font-medium tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-950">
                                {filteredClients.length > 0 ? (
                                    filteredClients.map((client) => (
                                        <tr key={client.client_id} className="transition-colors hover:bg-gray-50 dark:hover:bg-white/15">
                                            <td className="px-6 py-4">
                                                <div className="flex items-center">
                                                    <div className="mr-3 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-600">
                                                        {client.client_name
                                                            .split(' ')
                                                            .map((n) => n[0])
                                                            .join('')
                                                            .toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="font-medium text-gray-900 dark:text-gray-200">{client.client_name}</div>
                                                        <div className="text-sm text-gray-500 dark:text-gray-300">{client.client_reference}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="space-y-1">
                                                    <div className="flex items-center text-sm text-gray-600 dark:text-gray-200">
                                                        <Mail className="mr-2 h-4 w-4 text-gray-400" />
                                                        {client.client_email}
                                                    </div>
                                                    <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                        <Phone className="mr-2 h-4 w-4 text-gray-400" />
                                                        {client.client_phone}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-start text-sm text-gray-600 dark:text-gray-200">
                                                    <MapPin className="mt-0.5 mr-2 h-4 w-4 flex-shrink-0 text-gray-400" />
                                                    <div>
                                                        <div>{client.client_adress}</div>
                                                        {client.client_city && (
                                                            <div className="text-gray-500 dark:text-gray-300">{client.client_city}, </div>
                                                        )}
                                                        <div className="text-gray-500 dark:text-gray-300">{client.client_country}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${getStatusBadge(client.client_status)}`}
                                                >
                                                    {getStatusLabel(client.client_status)}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <button
                                                        className="rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/20"
                                                        title="Voir"
                                                    >
                                                        <Eye className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        className="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50"
                                                        title="Modifier"
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        className="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50"
                                                        title="Supprimer"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-8 text-center text-gray-500 dark:text-gray-300">
                                            Aucun client trouvé
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    <div className="flex flex-col items-center justify-between gap-4 border-t border-gray-200 px-6 py-4 sm:flex-row dark:border-gray-500">
                        <div className="text-sm text-gray-600 dark:text-gray-300">
                            Affichage de <span className="font-medium">{pagination.from}</span> à <span className="font-medium">{pagination.to}</span>{' '}
                            sur <span className="font-medium">{pagination.total}</span> résultat(s)
                        </div>
                        <div className="flex items-center gap-2">
                            <button
                                className="flex cursor-pointer items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-900"
                                disabled={pagination.current_page === 1}
                                onClick={() => handlePageChange(pagination.current_page - 1)}
                            >
                                <ChevronLeft className="h-4 w-4" />
                                Précédent
                            </button>

                            <div className="flex gap-1">
                                {[...Array(pagination.last_page)].map((_, i) => (
                                    <button
                                        key={i + 1}
                                        className={`rounded-lg px-3 py-2 text-sm ${
                                            pagination.current_page === i + 1
                                                ? 'bg-blue-600 text-white'
                                                : 'cursor-pointer border border-gray-300 hover:bg-gray-50 dark:hover:bg-slate-900'
                                        }`}
                                        onClick={() => handlePageChange(i + 1)}
                                    >
                                        {i + 1}
                                    </button>
                                ))}
                            </div>

                            <button
                                className="flex cursor-pointer items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-slate-900"
                                disabled={pagination.current_page === pagination.last_page}
                                onClick={() => handlePageChange(pagination.current_page + 1)}
                            >
                                Suivant
                                <ChevronRight className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default ClientPage;
