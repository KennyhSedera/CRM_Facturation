import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import TextInput from '@/components/ui/text-input';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const { data, setData, post, processing, errors, reset, setError } = useForm<Required<RegisterForm>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Créer une compte" description="">
            <Head title="Register" />
            <form className="flex flex-col gap-6 rounded-md bg-gray-100 px-8 py-4 dark:bg-white/15" onSubmit={submit}>
                <div className="grid gap-4">
                    <div className="grid gap-2">
                        <TextInput
                            label="Name"
                            id="name"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                            placeholder="Full name"
                            error={errors.name}
                            onFocus={() => setError('name', '')}
                        />
                    </div>

                    <div className="grid gap-2">
                        <TextInput
                            label="Addresse email"
                            id="email"
                            type="email"
                            required
                            tabIndex={2}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                            placeholder="email@example.com"
                            error={errors.email}
                            onFocus={() => setError('email', '')}
                        />
                    </div>

                    <div className="grid gap-2">
                        <TextInput
                            label="Mot de passe"
                            id="password"
                            type="password"
                            required
                            tabIndex={3}
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder="Password"
                            error={errors.password}
                            onFocus={() => setError('password', '')}
                        />
                    </div>

                    <div className="grid gap-2">
                        <TextInput
                            label="Confirmer le mot de passe"
                            id="password_confirmation"
                            type="password"
                            required
                            tabIndex={4}
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder="Confirm password"
                            error={errors.password_confirmation}
                            onFocus={() => setError('password_confirmation', '')}
                        />
                    </div>

                    <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Register
                    </Button>
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    Avez-vous déjà un compte?{' '}
                    <TextLink href={route('login')} tabIndex={6}>
                        Se connecter
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
