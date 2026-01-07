import React from 'react';

interface Props {
    logo?: string;
    title?: string;
}
export const AppLogo: React.FC<Props> = ({ logo, title }) => {
    return (
        <>
            <div className="flex aspect-square size-12 items-center justify-center rounded-md text-sidebar-primary-foreground">
                <img src={logo} alt={`${title || 'FacturePro'} logo`} className="h-full w-full rounded-md object-cover" />
            </div>
            <div className="ml-2 grid flex-1 text-left text-base">
                <span className="mb-0.5 truncate leading-tight font-semibold">{title || 'FacturePro'}</span>
            </div>
        </>
    );
};
