import { PageProps } from '@/types';
import { Head as InertiaHead, usePage } from '@inertiajs/react';
import React from 'react';

type HeadProps = {
    title: string;
    favicon?: string;
};

const Head: React.FC<HeadProps> = ({ title, favicon }) => {
    const page = usePage<PageProps>();
    const company = page.props.auth?.user?.company;

    const fullTitle = company?.company_name ? `${title} - ${company.company_name}` : `${title} - FacturePro`;

    const faviconUrl = company?.company_logo ? `/storage/${company.company_logo}` : favicon || '/facture-pro.png';

    return (
        <InertiaHead>
            <title>{fullTitle}</title>
            <link rel="icon" type="image/png" href={faviconUrl} />
        </InertiaHead>
    );
};

export default Head;
