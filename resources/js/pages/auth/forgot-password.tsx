import { Head } from '@inertiajs/react';

import TextLink from '@/components/text-link';
import { AuthCard } from '@/components/auth/auth-card';
import { AuthForm, AuthFormField } from '@/components/auth/auth-form';
import { AuthInput } from '@/components/auth/auth-input';
import { AuthButton } from '@/components/auth/auth-button';

type ForgotPasswordForm = {
    email: string;
};

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const handleSubmit = (data: ForgotPasswordForm, form: any) => {
        form.post(route('global.password.email'));
    };

    return (
        <>
            <Head title="Forgot password" />
            <AuthCard 
                title="Forgot password" 
                description="Enter your email address and we'll send you a password reset link"
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
                            </div>

                            <AuthButton
                                type="submit"
                                className="w-full"
                                tabIndex={2}
                                loading={form.processing}
                                loadingText="Sending reset link..."
                                aria-describedby={form.hasErrors ? "form-errors" : undefined}
                            >
                                Email password reset link
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
                                Remember your password?{' '}
                                <TextLink href={route('global.login')} tabIndex={3}>
                                    Back to log in
                                </TextLink>
                            </div>
                        </>
                    )}
                </AuthForm>
            </AuthCard>
        </>
    );
}
