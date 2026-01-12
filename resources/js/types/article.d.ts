import { Company, User } from ".";


export interface Article {
    article_id: number;
    article_reference: string;
    article_name: string;
    article_source: string;
    article_unité: string;
    selling_price: number;
    article_tva: number;
    quantity_stock: number;
    user_id: number;
    user: User;
    company_id: number;
    company: Company;
    created_at: string;
    updated_at: string;
}
