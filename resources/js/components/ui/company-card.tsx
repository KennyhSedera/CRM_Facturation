import React from 'react';
import { Building2, Mail, Phone, Globe, MapPin, Users, Calendar, CreditCard, Eye, Edit, MoreVertical } from 'lucide-react';
import { Company } from '@/types';
import { FaCheck, FaCheckToSlot, FaCircleCheck } from 'react-icons/fa6';
import { IoIosCloseCircle } from 'react-icons/io';

interface CompanyCardProps {
    company: Company;
    onView?: (companyId: number) => void;
    onEdit?: (companyId: number) => void;
    onMore?: (companyId: number) => void;
}

const CompanyCard: React.FC<CompanyCardProps> = ({ company, onView, onEdit, onMore }) => {
    const getPlanStatusColor = (status: string): string => {
        const colors: Record<string, string> = {
            premium: 'bg-purple-100 text-purple-700 border-purple-200',
            basic: 'bg-blue-100 text-blue-700 border-blue-200',
            trial: 'bg-gray-100 text-gray-700 border-gray-200',
            free: 'bg-gray-100 text-gray-700 border-gray-200',
            entreprise: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        };
        return colors[status] || colors.basic;
    };

    const formatDate = (dateString?: string): string => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    return (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:shadow-md dark:border-gray-500 dark:bg-white/15">
            <div className="p-6">
                {/* En-tête de la carte */}
                <div className="mb-6 flex items-start justify-between">
                    <div className="flex items-center gap-4">
                        <div className='relative'>
                            <span className='absolute -right-1 -bottom-1'>
                                {company.is_active ? <FaCircleCheck className='text-green-400 bg-white rounded-full size-5' />:
                                <IoIosCloseCircle className='text-red-500 bg-white rounded-full size-5' />}
                            </span>
                        {company.company_logo ?
                        <img
                        src={`/storage/${company.company_logo}`}
                        className=' h-16 w-16 rounded-xl'
                        alt="logo entreprise"
                         /> :
                        <div className="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg">
                            <Building2 className="h-8 w-8 text-white" />
                        </div>}
                        </div>
                        <div>
                            <h2 className="mb-1 text-xl font-bold text-slate-800 dark:text-white">{company.company_name}</h2>
                            <span
                                className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium ${getPlanStatusColor(company.plan_status)}`}
                            >
                                {company.plan_status.toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => onView?.(company.company_id)}
                            className="cursor-pointer rounded-lg p-2 transition-colors hover:bg-slate-100 dark:hover:bg-slate-900"
                        >
                            <Eye className="h-5 w-5 text-slate-600 dark:text-gray-300" />
                        </button>
                        {/* <button
                            onClick={() => onEdit?.(company.company_id)}
                            className="cursor-pointer rounded-lg p-2 transition-colors hover:bg-slate-100"
                        >
                            <Edit className="h-5 w-5 text-slate-600" />
                        </button> */}
                        <button
                            onClick={() => onMore?.(company.company_id)}
                            className="cursor-pointer rounded-lg p-2 transition-colors hover:bg-slate-100 dark:hover:bg-slate-900"
                        >
                            <MoreVertical className="h-5 w-5 text-slate-600 dark:text-gray-300" />
                        </button>
                    </div>
                </div>

                {/* Description */}
                {company.company_description && <p className="mb-6 text-slate-600 dark:text-gray-300 line-clamp-3 text-justify">{company.company_description}</p>}

                {/* Informations principales */}
                <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div className="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-black p-3">
                        <div className="rounded-lg bg-blue-100 p-2">
                            <Mail className="h-4 w-4 text-blue-600" />
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="mb-0.5 text-xs text-slate-500 dark:text-gray-400">Email</p>
                            <p className="truncate text-sm font-medium  text-slate-700 dark:text-gray-300">{company.company_email}</p>
                        </div>
                    </div>

                    {company.company_phone && (
                        <div className="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-black p-3">
                            <div className="rounded-lg bg-green-100 p-2">
                                <Phone className="h-4 w-4 text-green-600" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="mb-0.5 text-xs text-slate-500 dark:text-gray-400">Téléphone</p>
                                <p className="text-sm font-medium  text-slate-700 dark:text-gray-300">{company.company_phone}</p>
                            </div>
                        </div>
                    )}

                    {company.company_website && (
                        <div className="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-black p-3">
                            <div className="rounded-lg bg-purple-100 p-2">
                                <Globe className="h-4 w-4 text-purple-600" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="mb-0.5 text-xs text-slate-500 dark:text-gray-400">Site web</p>
                                <p className="truncate text-sm font-medium  text-slate-700 dark:text-gray-300">{company.company_website}</p>
                            </div>
                        </div>
                    )}

                    {(company.company_address || company.company_country) && (
                        <div className="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-black p-3">
                            <div className="rounded-lg bg-orange-100 p-2">
                                <MapPin className="h-4 w-4 text-orange-600" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="mb-0.5 text-xs text-slate-500 dark:text-gray-400">Localisation</p>
                                <p className="text-sm font-medium  text-slate-700 dark:text-gray-300">
                                    {[company.company_address, company.company_country].filter(Boolean).join(', ')}
                                </p>
                            </div>
                        </div>
                    )}

                    {company.client_count !== undefined && (
                        <div className="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-black p-3">
                            <div className="rounded-lg bg-pink-100 p-2">
                                <Users className="h-4 w-4 text-pink-600" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="mb-0.5 text-xs text-slate-500 dark:text-gray-400">Clients</p>
                                <p className="text-sm font-medium  text-slate-700 dark:text-gray-300">{company.client_count} clients</p>
                            </div>
                        </div>
                    )}

                    {company.company_currency && (
                        <div className="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-black p-3">
                            <div className="rounded-lg bg-indigo-100 p-2">
                                <CreditCard className="h-4 w-4 text-indigo-600" />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="mb-0.5 text-xs text-slate-500 dark:text-gray-400">Devise</p>
                                <p className="text-sm font-medium  text-slate-700 dark:text-gray-300">{company.company_currency}</p>
                            </div>
                        </div>
                    )}
                </div>

                {/* Footer de la carte */}
                <div className="flex items-center justify-between border-t border-slate-100 dark:border-gray-500 pt-4">
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <Calendar className="h-4 w-4 text-slate-400 dark:text-gray-400" />
                            <span className="text-sm text-slate-600 dark:text-gray-200">
                                Plan: {formatDate(company.plan_start_date)} - {formatDate(company.plan_end_date)}
                            </span>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        {company.users && Array.isArray(company.users) ? (
                            <>
                                {company.users.slice(0, 3).map((user) => (
                                    <div
                                        key={user.id}
                                        className="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-400 to-purple-500 text-xs font-medium text-white shadow-sm"
                                        title={user.name}
                                    >
                                        {user.name.charAt(0)}
                                    </div>
                                ))}
                                {company.users.length > 3 && (
                                    <div className="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-200 text-xs font-medium text-slate-600 shadow-sm">
                                        +{company.users.length - 3}
                                    </div>
                                )}
                            </>
                        ) : (
                            company.users && (
                                <div
                                    className="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-400 to-purple-500 text-xs font-medium text-white shadow-sm"
                                    title={company.users.name}
                                >
                                    {company.users.name.charAt(0)}
                                </div>
                            )
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default CompanyCard;
