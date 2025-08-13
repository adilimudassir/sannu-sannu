import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { AuthCard } from '@/components/auth/auth-card';
import { AuthForm, AuthFormField } from '@/components/auth/auth-form';
import { AuthInput } from '@/components/auth/auth-input';
import { AuthButton } from '@/components/auth/auth-button';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    errors?: Record<string, string>;
}

export default function Login({ status, canResetPassword, errors: serverErrors }: LoginProps) {
    const handleSubmit = (data: LoginForm, form: any) => {
        form.post(route('login.store'), {
            onFinish: () => form.reset('password'),
        });
    };

    return (
        <>
            <Head title="Log in" />
            <AuthCard 
                title="Log in to your account" 
                description="Enter your email and password below to log in"
            >
                {/* Status message */}
                {status && (
                    <div 
                        className="mb-4 text-center text-sm font-medium text-green-600"
                        role="status"
                        aria-live="polite"
                    >
                        {status}
                    </div>
                )}

                <AuthForm
                    initialData={{
                        email: '',
                        password: '',
                        remember: false,
                    }}
                    onSubmit={handleSubmit}
                >
                    {(form) => (
                        <>
                            <div className="space-y-4">
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
                                        autoFocus
                                        tabIndex={1}
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
                                    <div className="space-y-2">
                                        {canResetPassword && (
                                            <div className="flex items-center justify-between">
                                                <span></span>
                                                <TextLink 
                                                    href={route('password.request')} 
                                                    className="text-sm" 
                                                    tabIndex={5}
                                                >
                                                    Forgot password?
                                                </TextLink>
                                            </div>
                                        )}
                                        <AuthInput
                                            id="password"
                                            name="password"
                                            type="password"
                                            required
                                            tabIndex={2}
                                            autoComplete="current-password"
                                            value={form.data.password}
                                            onChange={(e) => form.setData('password', e.target.value)}
                                            placeholder="Password"
                                            error={!!form.errors.password}
                                            aria-describedby={form.errors.password ? "password-error" : undefined}
                                        />
                                    </div>
                                </AuthFormField>

                                <div className="flex items-center space-x-3">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        checked={form.data.remember}
                                        onCheckedChange={(checked) => form.setData('remember', !!checked)}
                                        tabIndex={3}
                                        aria-describedby="remember-description"
                                    />
                                    <Label htmlFor="remember" className="text-sm font-normal">
                                        Remember me
                                    </Label>
                                </div>
                                <span id="remember-description" className="sr-only">
                                    Keep me logged in on this device
                                </span>
                            </div>

                            <AuthButton
                                type="submit"
                                className="w-full"
                                tabIndex={4}
                                loading={form.processing}
                                loadingText="Signing in..."
                                aria-describedby={form.hasErrors ? "form-errors" : undefined}
                            >
                                Log in
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
                                Don't have an account?{' '}
                                <TextLink href={route('register')} tabIndex={6}>
                                    Sign up
                                </TextLink>
                            </div>
                        </>
                    )}
                </AuthForm>
            </AuthCard>
        </>
    );
}
