import { Head, Link } from '@inertiajs/react';

interface Props {
    status: number;
    message?: string;
}

export default function Error500({ status, message }: Props) {
    return (
        <>
            <Head title="500 - Erreur serveur" />

            <div className="flex min-h-screen items-center justify-center bg-gray-100 dark:bg-gray-900">
                <div className="w-full max-w-md rounded-lg bg-white px-6 py-8 text-center shadow-md dark:bg-gray-800">
                    <div className="mb-4 text-6xl font-bold text-yellow-500">{status}</div>
                    <h1 className="mb-2 text-2xl font-semibold text-gray-900 dark:text-white">Erreur serveur</h1>
                    <p className="mb-6 text-gray-600 dark:text-gray-400">{message || "Une erreur s'est produite. Veuillez réessayer plus tard."}</p>
                    <div className="space-x-4">
                        <Link href="/" className="inline-block rounded-md bg-blue-600 px-6 py-2 text-white transition hover:bg-blue-700">
                            Retour à l'accueil
                        </Link>
                        <button
                            onClick={() => window.location.reload()}
                            className="inline-block rounded-md bg-gray-600 px-6 py-2 text-white transition hover:bg-gray-700"
                        >
                            Recharger
                        </button>
                    </div>
                </div>
            </div>
        </>
    );
}
