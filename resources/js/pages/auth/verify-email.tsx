import { Head, useForm } from '@inertiajs/react';
import { CheckCircle, Clock, Mail, RefreshCw } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';

import { AuthCard } from '@/components/auth/auth-card';
import { AuthButton } from '@/components/auth/auth-button';
import TextLink from '@/components/text-link';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface VerifyEmailProps {
    status?: string;
    user: {
        name: string;
        email: string;
    };
}

export default function VerifyEmail({ status, user }: VerifyEmailProps) {
    const { post, processing } = useForm({});
    const [timeRemaining, setTimeRemaining] = useState<number | null>(null);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    // Handle throttling countdown
    useEffect(() => {
        if (status === 'verification-throttled') {
            setTimeRemaining(60);
            const interval = setInterval(() => {
                setTimeRemaining((prev) => {
                    if (prev === null || prev <= 1) {
                        clearInterval(interval);
                        return null;
                    }
                    return prev - 1;
                });
            }, 1000);

            return () => clearInterval(interval);
        }
    }, [status]);

    const getStatusMessage = () => {
        switch (status) {
            case 'verification-link-sent':
                return {
                    type: 'success' as const,
                    icon: <CheckCircle className="h-4 w-4" />,
                    message: 'A new verification link has been sent to your email address.'
                };
            case 'verification-throttled':
                return {
                    type: 'warning' as const,
                    icon: <Clock className="h-4 w-4" />,
                    message: `Please wait ${timeRemaining} seconds before requesting another verification email.`
                };
            default:
                return null;
        }
    };

    const statusMessage = getStatusMessage();

    return (
        <>
            <Head title="Verify Email Address" />
            
            <AuthCard
                title="Verify Your Email Address"
                description="We've sent a verification link to your email address. Please check your inbox and click the link to verify your account."
            >
                <div className="space-y-6">
                    {/* User info display */}
                    <div className="rounded-lg bg-muted/50 p-4 text-center">
                        <div className="flex items-center justify-center mb-2">
                            <Mail className="h-5 w-5 text-muted-foreground mr-2" />
                            <span className="text-sm font-medium text-muted-foreground">
                                Verification email sent to:
                            </span>
                        </div>
                        <p className="font-medium text-foreground">{user.email}</p>
                    </div>

                    {/* Status messages */}
                    {statusMessage && (
                        <Alert className={statusMessage.type === 'success' ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50'}>
                            <div className="flex items-center">
                                {statusMessage.icon}
                                <AlertDescription className="ml-2">
                                    {statusMessage.message}
                                </AlertDescription>
                            </div>
                        </Alert>
                    )}

                    {/* Instructions */}
                    <div className="text-center space-y-3">
                        <p className="text-sm text-muted-foreground">
                            Didn't receive the email? Check your spam folder or request a new verification link.
                        </p>
                        
                        <div className="text-xs text-muted-foreground">
                            <p>• Verification links expire after 60 minutes</p>
                            <p>• You can request a new link if the current one expires</p>
                        </div>
                    </div>

                    {/* Resend form */}
                    <form onSubmit={submit} className="space-y-4">
                        <AuthButton
                            type="submit"
                            disabled={processing || (status === 'verification-throttled' && timeRemaining !== null)}
                            loading={processing}
                            variant="secondary"
                            className="w-full"
                        >
                            <RefreshCw className="h-4 w-4 mr-2" />
                            {processing ? 'Sending...' : 'Resend Verification Email'}
                        </AuthButton>

                        <div className="text-center">
                            <TextLink 
                                href={route('logout')} 
                                method="post" 
                                className="text-sm text-muted-foreground hover:text-foreground"
                            >
                                Sign out and use a different account
                            </TextLink>
                        </div>
                    </form>

                    {/* Help text */}
                    <div className="text-center pt-4 border-t">
                        <p className="text-xs text-muted-foreground">
                            Having trouble? Contact support for assistance with email verification.
                        </p>
                    </div>
                </div>
            </AuthCard>
        </>
    );
}
