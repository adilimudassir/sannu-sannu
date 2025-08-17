import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Building2, Calendar, CheckCircle2, Clock, FileCheck, Mail, Phone, User, Globe, XCircle } from 'lucide-react';
import React, { useState } from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/formatters';

interface TenantApplication {
    id: number;
    reference_number: string;
    organization_name: string;
    contact_person_name: string;
    contact_person_email: string;
    contact_person_phone?: string;
    business_registration_number: string;
    industry_type: string;
    website_url?: string;
    status: 'pending' | 'approved' | 'rejected';
    submitted_at: string;
    reviewed_at?: string;
    reviewer?: string;
    rejection_reason?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
}

interface PageProps {
    [key: string]: any;
    application: TenantApplication;
}

export default function TenantApplicationShow() {
    const { application } = usePage<PageProps>().props;

    const [notes, setNotes] = useState('');
    const [rejectionReason, setRejectionReason] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const { status } = application;
    const canReview = status === 'pending';

    const handleApprove = (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        router.patch(`/admin/tenant-applications/${application.id}/approve`, { notes }, {
            onFinish: () => setSubmitting(false),
        });
    };

    const handleReject = (e: React.FormEvent) => {
        e.preventDefault();
        setSubmitting(true);
        router.patch(`/admin/tenant-applications/${application.id}/reject`, { rejection_reason: rejectionReason, notes }, {
            onFinish: () => setSubmitting(false),
        });
    };

    const statusConfig: Record<TenantApplication['status'], {
        label: string;
        icon: React.ElementType;
        color: string;
    }> = {
        pending: { label: 'Pending', icon: Clock, color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400' },
        approved: { label: 'Approved', icon: CheckCircle2, color: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400' },
        rejected: { label: 'Rejected', icon: XCircle, color: 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400' },
    };

    return (
        <AppLayout>
            <Head title={`Review Application: ${application.organization_name}`} />
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <Button variant="ghost" size="icon" asChild>
                                <Link href={route('admin.tenant-applications.index')}>
                                    <ArrowLeft className="h-4 w-4" />
                                </Link>
                            </Button>
                            <h1 className="text-3xl font-bold tracking-tight">Review Tenant Application</h1>
                        </div>
                        <p className="text-muted-foreground">Review and manage tenant application details</p>
                    </div>
                </div>

                {/* Application Overview */}
                <Card>
                    <CardHeader>
                        <CardTitle>Application Details</CardTitle>
                        <CardDescription>
                            Reference Number: <code className="font-mono">{application.reference_number}</code>
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Organization Information */}
                        <div className="space-y-2">
                            <h3 className="font-semibold">Organization Information</h3>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <Building2 className="h-4 w-4 text-muted-foreground" />
                                        <div className="font-medium">{application.organization_name}</div>
                                    </div>
                                    <div className="text-sm text-muted-foreground pl-6">
                                        Industry: {application.industry_type}
                                    </div>
                                </div>
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <FileCheck className="h-4 w-4 text-muted-foreground" />
                                        <span className="font-mono text-sm">{application.business_registration_number}</span>
                                    </div>
                                    {application.website_url && (
                                        <div className="flex items-center gap-2 pl-6">
                                            <Globe className="h-3 w-3 text-muted-foreground" />
                                            <a href={application.website_url} target="_blank" rel="noopener noreferrer" className="text-sm text-primary hover:underline">
                                                {application.website_url}
                                            </a>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        <Separator />

                        {/* Contact Information */}
                        <div className="space-y-2">
                            <h3 className="font-semibold">Contact Information</h3>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-1">
                                    <div className="flex items-center gap-2">
                                        <User className="h-4 w-4 text-muted-foreground" />
                                        <div className="font-medium">{application.contact_person_name}</div>
                                    </div>
                                    <div className="flex items-center gap-2 pl-6 text-sm text-muted-foreground">
                                        <Mail className="h-3 w-3" />
                                        <span>{application.contact_person_email}</span>
                                    </div>
                                </div>
                                {application.contact_person_phone && (
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-2">
                                            <Phone className="h-4 w-4 text-muted-foreground" />
                                            <div>{application.contact_person_phone}</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        <Separator />

                        {/* Status Information */}
                        <div className="space-y-2">
                            <h3 className="font-semibold">Application Status</h3>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <div className="flex items-center gap-2">
                                        <Badge className={statusConfig[application.status].color}>
                                            {React.createElement(statusConfig[application.status].icon, { className: 'mr-1 h-3 w-3' })}
                                            {statusConfig[application.status].label}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Calendar className="h-3 w-3" />
                                        <span>Submitted {formatDate(application.submitted_at)}</span>
                                    </div>
                                </div>
                                {application.reviewed_at && (
                                    <div className="space-y-1">
                                        <div className="text-sm text-muted-foreground">
                                            Reviewed {formatDate(application.reviewed_at)}
                                            {application.reviewer && ` by ${application.reviewer}`}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {application.rejection_reason && (
                                <Alert variant="destructive" className="mt-4">
                                    <XCircle className="h-4 w-4" />
                                    <AlertTitle>Application Rejected</AlertTitle>
                                    <AlertDescription>{application.rejection_reason}</AlertDescription>
                                </Alert>
                            )}

                            {application.notes && (
                                <div className="mt-4 rounded-lg border p-4">
                                    <h4 className="font-medium mb-2">Review Notes</h4>
                                    <p className="text-sm text-muted-foreground">{application.notes}</p>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Review Actions */}
                {canReview && (
                    <div className="grid gap-6 md:grid-cols-2">
                        <Card>
                            <form onSubmit={handleApprove}>
                                <CardHeader>
                                    <CardTitle className="text-green-700 dark:text-green-400">Approve Application</CardTitle>
                                    <CardDescription>Grant tenant access to the platform</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="approve-notes">Notes (optional)</Label>
                                        <Textarea
                                            id="approve-notes"
                                            placeholder="Add any notes about this approval..."
                                            value={notes}
                                            onChange={e => setNotes(e.target.value)}
                                        />
                                    </div>
                                    <Button type="submit" className="w-full" disabled={submitting}>
                                        <CheckCircle2 className="mr-2 h-4 w-4" />
                                        Approve Application
                                    </Button>
                                </CardContent>
                            </form>
                        </Card>

                        <Card>
                            <form onSubmit={handleReject}>
                                <CardHeader>
                                    <CardTitle className="text-red-700 dark:text-red-400">Reject Application</CardTitle>
                                    <CardDescription>Decline tenant access to the platform</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="rejection-reason">Rejection Reason</Label>
                                        <Input
                                            id="rejection-reason"
                                            placeholder="Provide a reason for rejection..."
                                            value={rejectionReason}
                                            onChange={e => setRejectionReason(e.target.value)}
                                            required
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="reject-notes">Additional Notes (optional)</Label>
                                        <Textarea
                                            id="reject-notes"
                                            placeholder="Add any additional notes..."
                                            value={notes}
                                            onChange={e => setNotes(e.target.value)}
                                        />
                                    </div>
                                    <Button type="submit" variant="destructive" className="w-full" disabled={submitting}>
                                        <XCircle className="mr-2 h-4 w-4" />
                                        Reject Application
                                    </Button>
                                </CardContent>
                            </form>
                        </Card>
                    </div>
                )}

                {status === 'approved' && (
                    <Alert className="bg-green-100 dark:bg-green-800/20 text-green-800 dark:text-green-400 border-green-200 dark:border-green-800">
                        <CheckCircle2 className="h-4 w-4" />
                        <AlertTitle>Application Approved</AlertTitle>
                        <AlertDescription>This tenant application has been approved and can now access the platform.</AlertDescription>
                    </Alert>
                )}

                {status === 'rejected' && (
                    <Alert variant="destructive">
                        <XCircle className="h-4 w-4" />
                        <AlertTitle>Application Rejected</AlertTitle>
                        <AlertDescription>This tenant application has been rejected.</AlertDescription>
                    </Alert>
                )}
            </div>
        </AppLayout>
    );
}
