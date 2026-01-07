<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'utilisateur admin ou le créer s'il n'existe pas
        $user = User::where('email', 'admin@dargatech.com')->first()
               ?? User::factory()->create([
                   'name' => 'Admin User',
                   'email' => 'admin@dargatech.com',
               ]);

        // Créer des clients
        $clients = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@email.com',
                'phone' => '0123456789',
                'address' => '123 Rue de la Paix, 75001 Paris'
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@email.com',
                'phone' => '0987654321',
                'address' => '456 Avenue des Champs, 69000 Lyon'
            ],
            [
                'name' => 'Paul Durand',
                'email' => 'paul.durand@email.com',
                'phone' => '0147258369',
                'address' => '789 Boulevard Saint-Michel, 13000 Marseille'
            ]
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }

        // Créer des produits
        $products = [
            [
                'name' => 'Ordinateur portable Dell',
                'description' => 'Ordinateur portable Dell Inspiron 15, Intel i5, 8GB RAM, 256GB SSD',
                'price' => 899.99,
                'stock' => 15
            ],
            [
                'name' => 'Souris sans fil Logitech',
                'description' => 'Souris sans fil ergonomique avec capteur optique haute précision',
                'price' => 29.99,
                'stock' => 50
            ],
            [
                'name' => 'Clavier mécanique',
                'description' => 'Clavier mécanique rétroéclairé avec switches Cherry MX',
                'price' => 79.99,
                'stock' => 25
            ],
            [
                'name' => 'Écran 24 pouces',
                'description' => 'Écran LED 24 pouces Full HD, HDMI et DisplayPort',
                'price' => 199.99,
                'stock' => 8
            ],
            [
                'name' => 'Imprimante multifonctions',
                'description' => 'Imprimante jet d\'encre multifonctions, WiFi, recto-verso',
                'price' => 149.99,
                'stock' => 12
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Créer des devis
        $client1 = Client::first();
        $client2 = Client::find(2);
        $client3 = Client::find(3);

        // Devis 1
        $quote1 = Quote::create([
            'client_id' => $client1->id,
            'user_id' => $user->id,
            'date' => now()->subDays(10),
            'total' => 1179.97,
            'status' => 'sent'
        ]);

        QuoteItem::create([
            'quote_id' => $quote1->id,
            'product_id' => 1, // Ordinateur portable
            'quantity' => 1,
            'price' => 899.99
        ]);

        QuoteItem::create([
            'quote_id' => $quote1->id,
            'product_id' => 2, // Souris
            'quantity' => 2,
            'price' => 29.99
        ]);

        QuoteItem::create([
            'quote_id' => $quote1->id,
            'product_id' => 4, // Écran
            'quantity' => 1,
            'price' => 199.99
        ]);

        // Devis 2
        $quote2 = Quote::create([
            'client_id' => $client2->id,
            'user_id' => $user->id,
            'date' => now()->subDays(5),
            'total' => 429.97,
            'status' => 'accepted'
        ]);

        QuoteItem::create([
            'quote_id' => $quote2->id,
            'product_id' => 3, // Clavier
            'quantity' => 2,
            'price' => 79.99
        ]);

        QuoteItem::create([
            'quote_id' => $quote2->id,
            'product_id' => 4, // Écran
            'quantity' => 1,
            'price' => 199.99
        ]);

        QuoteItem::create([
            'quote_id' => $quote2->id,
            'product_id' => 2, // Souris
            'quantity' => 3,
            'price' => 29.99
        ]);

        // Devis 3
        $quote3 = Quote::create([
            'client_id' => $client3->id,
            'user_id' => $user->id,
            'date' => now()->subDays(2),
            'total' => 1049.98,
            'status' => 'draft'
        ]);

        QuoteItem::create([
            'quote_id' => $quote3->id,
            'product_id' => 1, // Ordinateur
            'quantity' => 1,
            'price' => 899.99
        ]);

        QuoteItem::create([
            'quote_id' => $quote3->id,
            'product_id' => 5, // Imprimante
            'quantity' => 1,
            'price' => 149.99
        ]);

        // Créer une facture basée sur le devis accepté
        $invoice1 = Invoice::create([
            'client_id' => $client2->id,
            'user_id' => $user->id,
            'quote_id' => $quote2->id,
            'date' => now()->subDays(3),
            'total' => 429.97,
            'status' => 'sent',
            'due_date' => now()->addDays(30)
        ]);

        // Copier les items du devis vers la facture
        foreach ($quote2->items as $quoteItem) {
            InvoiceItem::create([
                'invoice_id' => $invoice1->id,
                'product_id' => $quoteItem->product_id,
                'quantity' => $quoteItem->quantity,
                'price' => $quoteItem->price
            ]);
        }

        // Créer une facture directe (sans devis)
        $invoice2 = Invoice::create([
            'client_id' => $client1->id,
            'user_id' => $user->id,
            'quote_id' => null,
            'date' => now()->subDays(1),
            'total' => 259.97,
            'status' => 'paid',
            'due_date' => now()->addDays(30)
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => 2, // Souris
            'quantity' => 5,
            'price' => 29.99
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => 3, // Clavier
            'quantity' => 2,
            'price' => 79.99
        ]);
    }
}
