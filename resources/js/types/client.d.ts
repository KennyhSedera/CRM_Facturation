export interface Client {
    client_id: number;
    client_reference: string;
    client_name: string;
    client_email: string;
    client_phone: string;
    client_adress: string;
    client_city: string | null;
    client_country: string;
    client_cin: string;
    client_status: 'active' | 'inactive' | 'pending';
    client_note: string | null;
    created_at: string;
    updated_at: string;
}

export interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface ApiWrapper {
    success: boolean;
    message: string;
    data: ApiResponse;
}

export interface ApiResponse {
    current_page: number;
    data: Client[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}
