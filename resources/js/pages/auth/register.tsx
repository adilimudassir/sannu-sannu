import { Head } from '@inertiajs/react';

import TextLink from '@/components/text-link';
import { AuthCard } from '@/components/auth/auth-card';
import { AuthForm, AuthFormField } from '@/components/auth/auth-form';
import { AuthInput } from '@/components/auth/auth-input';
import { AuthButton } from '@/components/auth/auth-button';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function Register() {
    const handleSubmit = (data: RegisterForm, form: any) => {
        form.post(route('global.register.store'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Register" />
            <AuthCard 
                title="Create an account" 
                description="Enter your details below to create your global account"
            >
                <AuthForm
                    initialData={{
                        name: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                    }}
                    onSubmit={handleSubmit}
                >
                    {(form) => (
                        <>
                            <div className="space-y-4">
                                <AuthFormField
                                    label="Full name"
                                    name="name"
                                    error={form.errors.name}
                                    required
                                >
                                    <AuthInput
                                        id="name"
                                        name="name"
                                        type="text"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="name"
                                        value={form.data.name}
                                        onChange={(e) => form.setData('name', e.target.value)}
                                        placeholder="Full name"
                                        error={!!form.errors.name}
                                        aria-describedby={form.errors.name ? "name-error" : undefined}
                                    />
                                </AuthFormField>

                                <AuthFormField
                                    label="Email address"
                                    name="email"
                                    error={form.errors.email}
                                    required
                                >
                                    <AuthInput
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        tabIndex={2}
                                        autoComplete="email"
                                        value={form.data.email}
                                        onChange={(e) => form.setData('email', e.target.value)}
                                        placeholder="email@example.com"
                                        error={!!form.errors.email}
                                        aria-describedby={form.errors.email ? "email-error" : undefined}
                                    />
                                </AuthFormField>

                                <AuthFormField
                                    label="Password"
                                    name="password"
                                    error={form.errors.password}
                                    required
                                >
                                    <AuthInput
                                        id="password"
                                        name="password"
                                        type="password"
                                        required
                                        tabIndex={3}
                                        autoComplete="new-password"
                                        value={form.data.password}
                                        onChange={(e) => form.setData('password', e.target.value)}
                                        placeholder="Password"
                                        error={!!form.errors.password}
                                        aria-describedby={form.errors.password ? "password-error" : undefined}
                                    />
                                </AuthFormField>

                                <AuthFormField
                                    label="Confirm password"
                                    name="password_confirmation"
                                    error={form.errors.password_confirmation}
                                    required
                                >
                                    <AuthInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        required
                                        tabIndex={4}
                                        autoComplete="new-password"
                                        value={form.data.password_confirmation}
                                        onChange={(e) => form.setData('password_confirmation', e.target.value)}
                                        placeholder="Confirm password"
                                        error={!!form.errors.password_confirmation}
                                        aria-describedby={form.errors.password_confirmation ? "password-confirmation-error" : undefined}
                                    />
                                </AuthFormField>
                            </div>

                            <AuthButton
                                type="submit"
                                className="w-full"
                                tabIndex={5}
                                loading={form.processing}
                                loadingText="Creating account..."
                                aria-describedby={form.hasErrors ? "form-errors" : undefined}
                            >
                                Create account
                            </AuthButton>

                            {/* General form errors */}
                            {form.hasErrors && (
                                <div 
                                    id="form-errors" 
                                    className="text-center text-sm text-destructive"
                                    role="alert"
                                    aria-live="assertive"
                                >
                                    Please correct the errors above and try again.
                                </div>
                            )}

                            <div className="text-center text-sm text-muted-foreground">
                                Already have an account?{' '}
                                <TextLink href={route('global.login')} tabIndex={6}>
                                    Log in
                                </TextLink>
                            </div>
                        </>
                    )}
                </AuthForm>
            </AuthCard>
        </>
    );
}
