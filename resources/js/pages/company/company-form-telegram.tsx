import { useEffect, useState } from 'react';

// Définir le type minimal pour Telegram WebApp
declare global {
    interface Window {
        Telegram?: {
            WebApp?: {
                expand: () => void;
                close: () => void;
                sendData: (data: string) => void;
                initData?: string;
                themeParams?: Record<string, string>;
            };
        };
    }
}

export default function CompabyFormTelegram() {
    const [email, setEmail] = useState<string>('');
    const [phone, setPhone] = useState<string>('');

    useEffect(() => {
        if (window.Telegram?.WebApp) {
            window.Telegram.WebApp.expand();
        }
    }, []);

    const handleSubmit = () => {
        if (!window.Telegram?.WebApp) return;

        const data = { email, phone };

        // Envoi des données au bot
        window.Telegram.WebApp.sendData(JSON.stringify(data));

        // Fermer la WebApp
        window.Telegram.WebApp.close();
    };

    return (
        <div style={{ padding: 20 }}>
            <h2>📋 Formulaire Telegram</h2>
            <input
                type="email"
                placeholder="Email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                style={{ display: 'block', marginBottom: 10, width: '100%' }}
            />
            <input
                type="tel"
                placeholder="Téléphone"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                style={{ display: 'block', marginBottom: 10, width: '100%' }}
            />
            <button onClick={handleSubmit}>Valider ✅</button>
        </div>
    );
}
