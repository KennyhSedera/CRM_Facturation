import React from 'react';
import { Check } from 'lucide-react';
import { Plan } from '@/types';

export const PricingCard: React.FC<Plan> = ({
  title,
  price,
  period = '/mois',
  description,
  features,
  buttonText,
  buttonVariant = 'default',
  badge,
  popular = false,
  onButtonClick
}) => {
  return (
    <div
      className={`relative flex flex-col p-8 rounded-2xl backdrop-blur-xl transition-all hover:backdrop-blur-2xl
        bg-white/10 hover:bg-white/20
        dark:bg-slate-800/10 dark:hover:bg-slate-800/20
        border
        ${
          popular
            ? 'border-blue-400/50 dark:border-blue-500/50 shadow-xl shadow-blue-500/20 dark:shadow-blue-400/30 scale-105'
            : 'border-white/20 dark:border-slate-700/30 shadow-lg shadow-black/5 dark:shadow-black/20'
        }`}
    >
      {badge && (
        <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
          <span className="backdrop-blur-md bg-gradient-to-r from-blue-500/90 to-purple-600/90 dark:from-blue-600/90 dark:to-purple-700/90 text-white px-4 py-1 rounded-full text-sm font-semibold shadow-lg border border-white/20 dark:border-white/30">
            {badge}
          </span>
        </div>
      )}

      <div className="mb-6">
        <h3 className="text-xl font-bold text-white mb-2 drop-shadow-sm">
          {title}
        </h3>
        <div className="flex items-baseline mb-2">
          <span className="text-4xl font-extrabold text-white drop-shadow-sm">
            {price}
          </span>
          <span className=" text-gray-300 ml-2">{period}</span>
        </div>
        <p className=" text-gray-300">{description}</p>
      </div>

      <ul className="mb-8 space-y-4 flex-grow">
        {features.map((feature, index) => (
          <li key={index} className="flex items-start">
            <div className="backdrop-blur-sm bg-green-400 rounded-full p-0.5 mr-3 flex-shrink-0 mt-0.5">
              <Check className="w-4 h-4 text-white" />
            </div>
            <span className=" text-gray-200">{feature}</span>
          </li>
        ))}
      </ul>

      <button
        onClick={onButtonClick}
        className={`w-full py-3 px-6 rounded-lg font-semibold transition-all backdrop-blur-md border cursor-pointer ${
          buttonVariant === 'primary'
            ? 'bg-gradient-to-r from-blue-500/90 to-purple-600/90 dark:from-blue-600/90 dark:to-purple-700/90 text-white hover:from-blue-600 hover:to-purple-700 dark:hover:from-blue-700 dark:hover:to-purple-800 shadow-lg hover:shadow-xl border-white/20 dark:border-white/30 hover:scale-[1.02]'
            : 'bg-white/30 dark:bg-slate-700/30 text-gray-900 dark:text-white hover:bg-white/40 dark:hover:bg-slate-700/40 border-white/30 dark:border-slate-600/30 hover:border-white/50 dark:hover:border-slate-600/50'
        }`}
      >
        {buttonText}
      </button>
    </div>
  );
};
