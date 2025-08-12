import { Head } from '@inertiajs/react';

import { AuthCard } from '@/components/auth/auth-card';
import { AuthForm, AuthFormField } from '@/components/auth/auth-form';
import { AuthInput } from '@/components/auth/auth-input';
import { AuthButton } from '@/components/auth/auth-button';

interface ResetPasswordProps {
    token: string;
    email: string;
}

type ResetPasswordForm = {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const handleSubmit = (data: ResetPasswordForm, form: any) => {
        form.post(route('global.password.store'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Reset password" />
            <AuthCard 
                title="Reset password" 
                description="Please enter your new password below"
            >
                <AuthForm
                    initialData={{
                        token: token,
                        email: email,
                        password: '',
                        password_confirmation: '',
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
                                >
                                    <AuthInput
                                        id="email"
                                        name="email"
                                        type="email"
                                        tabIndex={1}
                                        autoComplete="email"
                                        value={form.data.email}
                                        onChange={(e) => form.setData('email', e.target.value)}
                                        readOnly
                                        className="bg-muted"
                                        error={!!form.errors.email}
                                        aria-describedby={form.errors.email ? "email-error" : undefined}
                                    />
                                </AuthFormField>

                                <AuthFormField
                                    label="New password"
                                    name="password"
                                    error={form.errors.password}
                                    required
                                >
                                    <AuthInput
                                        id="password"
                                        name="password"
                                        type="password"
                                        required
                                        autoFocus
                                        tabIndex={2}
                                        autoComplete="new-password"
                                        value={form.data.password}
                                        onChange={(e) => form.setData('password', e.target.value)}
                                        placeholder="Enter your new password"
                                        error={!!form.errors.password}
                                        aria-describedby={form.errors.password ? "password-error" : undefined}
                                    />
                                </AuthFormField>

                                <AuthFormField
                                    label="Confirm new password"
                                    name="password_confirmation"
                                    error={form.errors.password_confirmation}
                                    required
                                >
                                    <AuthInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        required
                                        tabIndex={3}
                                        autoComplete="new-password"
                                        value={form.data.password_confirmation}
                                        onChange={(e) => form.setData('password_confirmation', e.target.value)}
                                        placeholder="Confirm your new password"
                                        error={!!form.errors.password_confirmation}
                                        aria-describedby={form.errors.password_confirmation ? "password-confirmation-error" : undefined}
                                    />
                                </AuthFormField>
                            </div>

                            <AuthButton
                                type="submit"
                                className="w-full"
                                tabIndex={4}
                                loading={form.processing}
                                loadingText="Resetting password..."
                                aria-describedby={form.hasErrors ? "form-errors" : undefined}
                            >
                                Reset password
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
                        </>
                    )}
                </AuthForm>
            </AuthCard>
        </>
    );
}
