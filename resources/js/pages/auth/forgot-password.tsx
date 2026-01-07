// Components
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import TextInput from '@/components/ui/text-input';
import AuthLayout from '@/layouts/auth-layout';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm<Required<{ email: string }>>({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <AuthLayout title="Forgot password" description="">
            <Head title="Forgot password" />

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}

            <div className="space-y-6 rounded-md bg-gray-100 p-8 dark:bg-white/15">
                <form onSubmit={submit}>
                    <div className="grid gap-2">
                        <TextInput
                            label="Addresse email"
                            id="email"
                            type="email"
                            name="email"
                            autoComplete="off"
                            value={data.email}
                            autoFocus
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="email@example.com"
                            error={errors.email}
                            required
                        />
                    </div>

                    <div className="my-6 flex items-center justify-start">
                        <Button className="w-full" disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Envoyer
                        </Button>
                    </div>
                </form>

                <div className="space-x-1 text-left text-sm text-muted-foreground">
                    <span>Ou, returner </span>
                    <TextLink href={route('login')}>se connecter</TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
