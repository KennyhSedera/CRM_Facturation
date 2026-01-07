import React from 'react';
import { LucideIcon } from 'lucide-react';

type IconColor = 'blue' | 'emerald' | 'purple' | 'orange' | 'pink' | 'indigo';

interface ColorClasses {
  hover: string;
  iconBg: string;
  iconColor: string;
}

interface FeatureCardProps {
  icon: LucideIcon;
  title: string;
  description: string;
  iconColor?: IconColor;
}

export const FeatureCard: React.FC<FeatureCardProps> = ({
  icon: Icon,
  title,
  description,
  iconColor = 'blue'
}) => {
  const colorClasses: Record<IconColor, ColorClasses> = {
    blue: {
      hover: 'hover:border-blue-400 dark:hover:border-blue-500/50',
      iconBg: 'bg-blue-100 dark:bg-blue-500/10',
      iconColor: 'text-blue-600 dark:text-blue-400'
    },
    emerald: {
      hover: 'hover:border-emerald-400 dark:hover:border-emerald-500/50',
      iconBg: 'bg-emerald-100 dark:bg-emerald-500/10',
      iconColor: 'text-emerald-600 dark:text-emerald-400'
    },
    purple: {
      hover: 'hover:border-purple-400 dark:hover:border-purple-500/50',
      iconBg: 'bg-purple-100 dark:bg-purple-500/10',
      iconColor: 'text-purple-600 dark:text-purple-400'
    },
    orange: {
      hover: 'hover:border-orange-400 dark:hover:border-orange-500/50',
      iconBg: 'bg-orange-100 dark:bg-orange-500/10',
      iconColor: 'text-orange-600 dark:text-orange-400'
    },
    pink: {
      hover: 'hover:border-pink-400 dark:hover:border-pink-500/50',
      iconBg: 'bg-pink-100 dark:bg-pink-500/10',
      iconColor: 'text-pink-600 dark:text-pink-400'
    },
    indigo: {
      hover: 'hover:border-indigo-400 dark:hover:border-indigo-500/50',
      iconBg: 'bg-indigo-100 dark:bg-indigo-500/10',
      iconColor: 'text-indigo-600 dark:text-indigo-400'
    }
  };

  const colors = colorClasses[iconColor];

  return (
    <div
      className={`group rounded-2xl border-2 border-gray-200 bg-white p-8 transition hover:shadow-xl dark:border-slate-700/50 dark:bg-gray-800 ${colors.hover}`}
    >
      <div
        className={`mb-6 flex h-14 w-14 items-center justify-center rounded-xl transition group-hover:scale-110 ${colors.iconBg}`}
      >
        <Icon className={`h-7 w-7 ${colors.iconColor}`} />
      </div>
      <h3 className="mb-3 text-2xl font-bold text-gray-900 dark:text-white">
        {title}
      </h3>
      <p className="text-gray-600 dark:text-slate-400">
        {description}
      </p>
    </div>
  );
};
