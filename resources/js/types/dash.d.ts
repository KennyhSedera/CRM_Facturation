
export interface Stat {
    title: string;
    value: string;
    change: string;
    changeType: 'positive' | 'negative';
    icon: string;
    bgColor: string;
}

export interface InvoiceStatus {
    status: string;
    count: number;
    amount: string;
    icon: string;
    color: string;
    bgColor: string;
}

export interface Activity {
    id: string;
    action: string;
    client: string;
    time: string;
    type: string;
}

export interface TopArticle {
    name: string;
    sales: number;
    revenue: string;
}

export interface TopClient {
    name: string;
    invoices: number;
    amount: string;
}

export interface RevenueData {
    date: string;
    montant: number;
}

export interface DashboardData {
    stats: Stat[];
    invoicesStatus: InvoiceStatus[];
    recentActivities: Activity[];
    topArticles: TopArticle[];
    topClients: TopClient[];
    revenueData: RevenueData[];
}

export interface QuickAction {
    label: string;
    icon: LucideIcon;
    href?: string;
    onClick?: () => void;
    colorClasses: {
        icon: string;
        hoverBorder: string;
        hoverBg: string;
    };
}
