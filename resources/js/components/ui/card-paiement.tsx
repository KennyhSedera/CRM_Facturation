import React from 'react'
import { GiReceiveMoney } from 'react-icons/gi'

interface CardPaiementProps {
    label: string
    icon: string
    selected: boolean
    onSelect: () => void
}

const CardPaiement = ({ label, icon, selected, onSelect }: CardPaiementProps) => {
    return (
        <div
            onClick={onSelect}
            className={`group relative cursor-pointer overflow-hidden rounded-2xl border-2 bg-white/80 p-6 shadow-sm backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:shadow-xl dark:bg-black/80 ${
                selected
                    ? 'border-green-500 dark:border-green-400'
                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'
            }`}
        >
            <div className="flex flex-col items-center gap-5">
                {/* Image avec effet de glow */}
                <div className="relative">
                    {label !== 'Autre' ? (
                        <img
                            src={icon}
                            alt={`logo ${label}`}
                            className={`relative h-32 w-32 rounded-full object-cover ring-4 transition-all duration-300 shadow ${
                                selected
                                    ? 'ring-green-500 group-hover:ring-green-500 dark:ring-green-400 dark:group-hover:ring-green-400'
                                    : 'ring-gray-100 group-hover:ring-8 dark:ring-gray-700 dark:group-hover:ring-gray-600'
                            }`}
                        />
                    ) : (
                        <div
                            className={`relative flex h-32 w-32 items-center justify-center rounded-full bg-gradient-to-br from-indigo-50 to-purple-50 ring-4 transition-all duration-300 dark:from-indigo-900/30 dark:to-purple-900/30 ${
                                selected
                                    ? 'ring-green-500 group-hover:ring-green-500 dark:ring-green-400 dark:group-hover:ring-green-400'
                                    : 'ring-gray-100 group-hover:ring-8 dark:ring-gray-700 dark:group-hover:ring-gray-600'
                            }`}
                        >
                            <GiReceiveMoney className="h-20 w-20 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    )}

                    {/* Badge de sélection avec animation */}
                    <div
                        className={`absolute bottom-1 right-1 flex h-8 w-8 items-center justify-center rounded-full shadow-md ring-2 transition-all duration-300 ${
                            selected
                                ? 'scale-100 bg-green-500 ring-white opacity-100 dark:bg-green-400 dark:ring-gray-800'
                                : 'scale-0 bg-gray-300 ring-gray-100 opacity-0 dark:bg-gray-600'
                        }`}
                    >
                        <span className="text-xs font-semibold text-white">✓</span>
                    </div>
                </div>

                {/* Label avec animation */}
                <div className="space-y-1 text-center">
                    <h3
                        className={`text-base font-bold transition-all duration-300 group-hover:scale-110 ${
                            selected
                                ? 'text-green-500 dark:text-green-400'
                                : 'text-gray-700 group-hover:text-gray-900 dark:text-gray-200 dark:group-hover:text-white'
                        }`}
                    >
                        {label}
                    </h3>
                    <p
                        className={`text-xs transition-opacity duration-300 group-hover:opacity-100 ${
                            selected
                                ? 'text-green-500 opacity-100 dark:text-green-400'
                                : 'text-gray-500 opacity-0 dark:text-gray-400'
                        }`}
                    >
                        {selected ? 'Sélectionné' : 'Sélectionner'}
                    </p>
                </div>
            </div>
        </div>
    )
}

export default CardPaiement
