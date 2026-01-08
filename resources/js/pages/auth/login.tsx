// resources/js/pages/auth/login.tsx

import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useEffect } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import TextInput from '@/components/ui/text-input';
import AuthLayout from '@/layouts/auth-layout';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    email?: string;
    password?: string;
    redirect_text?: string;
}

export default function Login({ status, canResetPassword, email, password, redirect_text }: LoginProps) {
    const { data, setData, post, processing, errors, reset, setError } = useForm<Required<LoginForm>>({
        email: email || '',
        password: password || '',
        remember: false,
    });

    // Pré-remplir les champs si les paramètres URL sont présents
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const urlEmail = urlParams.get('email');
        const urlPassword = urlParams.get('password');

        if (urlEmail) {
            setData('email', urlEmail);
        }
        if (urlPassword) {
            setData('password', urlPassword);
        }
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Se connecter avec votre compte" description="">
            <Head title="Log in" />

            {/* Message de redirection si présent */}
            {redirect_text && (
                <div className="mb-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                    <p className="font-medium">{redirect_text}</p>
                    {data.email && (
                        <p className="mt-1">
                            Email : <strong>{data.email}</strong>
                        </p>
                    )}
                </div>
            )}

            {status && (
                <div className="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400">{status}</div>
            )}

            <form className="flex w-auto flex-col gap-6 rounded-md bg-gray-100 p-8 dark:bg-white/15" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <TextInput
                            label="Adresse email"
                            id="email"
                            type="email"
                            required
                            autoFocus={!data.email} // Focus seulement si email vide
                            tabIndex={1}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="email@example.com"
                            error={errors.email}
                            onFocus={() => setError('email', '')}
                        />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            {canResetPassword && (
                                <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                    Mot de passe oublié ?
                                </TextLink>
                            )}
                        </div>
                        <TextInput
                            label="Mot de passe"
                            id="password"
                            type="password"
                            required
                            autoFocus={!!data.email} // Focus sur password si email pré-rempli
                            tabIndex={2}
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Mot de passe"
                            error={errors.password}
                            onFocus={() => setError('password', '')}
                        />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            onClick={() => setData('remember', !data.remember)}
                            tabIndex={3}
                            className="border-2 border-gray-400 dark:border-gray-100"
                        />
                        <Label htmlFor="remember">Se souvenir de moi</Label>
                    </div>

                    <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Se connecter
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
