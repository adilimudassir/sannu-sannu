import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { Clock, MapPin, Monitor, Shield, Smartphone, Tablet } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Active Sessions',
        href: '/settings/sessions',
    },
];

interface Session {
    id: string;
    ip_address: string;
    user_agent: string;
    last_activity: string;
    is_current: boolean;
    location: string;
}

interface Props {
    sessions: Session[];
}

export default function Sessions({ sessions }: Props) {
    const { delete: destroy, processing: destroying } = useForm();
    const { post, processing: revokingOthers } = useForm();

    const getDeviceIcon = (userAgent: string) => {
        const ua = userAgent.toLowerCase();
        if (ua.includes('mobile') || ua.includes('android') || ua.includes('iphone')) {
            return <Smartphone className="h-4 w-4" />;
        }
        if (ua.includes('tablet') || ua.includes('ipad')) {
            return <Tablet className="h-4 w-4" />;
        }
        return <Monitor className="h-4 w-4" />;
    };

    const getDeviceInfo = (userAgent: string) => {
        // Basic user agent parsing - in production you might want a more robust solution
        if (userAgent.includes('Chrome')) return 'Chrome';
        if (userAgent.includes('Firefox')) return 'Firefox';
        if (userAgent.includes('Safari')) return 'Safari';
        if (userAgent.includes('Edge')) return 'Edge';
        return 'Unknown Browser';
    };

    const handleRevokeSession = (sessionId: string) => {
        destroy(route('sessions.destroy', sessionId), {
            preserveScroll: true,
        });
    };

    const handleRevokeOthers = () => {
        post(route('sessions.destroy-others'), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Active Sessions" />
            <SettingsLayout>
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                Session Management
                            </CardTitle>
                            <CardDescription>
                                These are the devices that are currently logged into your account. Revoke any sessions that you do not recognize.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {sessions.length > 1 && (
                                <div className="flex justify-end">
                                    <Button variant="outline" onClick={handleRevokeOthers} disabled={revokingOthers}>
                                        {revokingOthers ? 'Revoking...' : 'Revoke All Other Sessions'}
                                    </Button>
                                </div>
                            )}

                            <div className="space-y-4">
                                {sessions.map((session, index) => (
                                    <div key={session.id}>
                                        <div className="flex items-start justify-between rounded-lg border p-4">
                                            <div className="flex items-start space-x-3">
                                                <div className="mt-1 flex-shrink-0">{getDeviceIcon(session.user_agent)}</div>
                                                <div className="min-w-0 flex-1">
                                                    <div className="mb-1 flex items-center gap-2">
                                                        <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {getDeviceInfo(session.user_agent)}
                                                        </p>
                                                        {session.is_current && (
                                                            <Badge variant="secondary" className="text-xs">
                                                                Current Session
                                                            </Badge>
                                                        )}
                                                    </div>

                                                    <div className="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <div className="flex items-center gap-1">
                                                            <MapPin className="h-3 w-3" />
                                                            <span>{session.ip_address}</span>
                                                            <span>•</span>
                                                            <span>{session.location}</span>
                                                        </div>
                                                        <div className="flex items-center gap-1">
                                                            <Clock className="h-3 w-3" />
                                                            <span>
                                                                Last active{' '}
                                                                {formatDistanceToNow(new Date(session.last_activity), { addSuffix: true })}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {!session.is_current && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleRevokeSession(session.id)}
                                                    disabled={destroying}
                                                >
                                                    {destroying ? 'Revoking...' : 'Revoke'}
                                                </Button>
                                            )}
                                        </div>

                                        {index < sessions.length - 1 && <Separator className="my-4" />}
                                    </div>
                                ))}
                            </div>

                            {sessions.length === 0 && (
                                <div className="py-8 text-center text-gray-500 dark:text-gray-400">
                                    <Shield className="mx-auto mb-4 h-12 w-12 opacity-50" />
                                    <p>No active sessions found.</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Security Tips</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <p>• Always log out from public or shared computers</p>
                            <p>• Regularly review your active sessions</p>
                            <p>• Revoke any sessions you don't recognize immediately</p>
                            <p>• Use strong, unique passwords for better security</p>
                        </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
