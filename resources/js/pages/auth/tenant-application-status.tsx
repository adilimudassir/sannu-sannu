import React from 'react';
import { Head } from '@inertiajs/react';
import { CheckCircle, Clock, XCircle, Copy, ExternalLink } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import TextLink from '@/components/text-link';

interface TenantApplicationStatusProps {
  application: {
    reference_number: string;
    organization_name: string;
    status: 'pending' | 'approved' | 'rejected';
    submitted_at: string;
    reviewed_at?: string;
    rejection_reason?: string;
  };
}

export default function TenantApplicationStatus({ application }: TenantApplicationStatusProps) {
  const copyReferenceNumber = () => {
    navigator.clipboard.writeText(application.reference_number);
  };

  const getStatusIcon = () => {
    switch (application.status) {
      case 'pending':
        return <Clock className="h-5 w-5 text-yellow-500" />;
      case 'approved':
        return <CheckCircle className="h-5 w-5 text-green-500" />;
      case 'rejected':
        return <XCircle className="h-5 w-5 text-red-500" />;
    }
  };

  const getStatusBadge = () => {
    switch (application.status) {
      case 'pending':
        return <Badge variant="secondary" className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">Pending Review</Badge>;
      case 'approved':
        return <Badge variant="secondary" className="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Approved</Badge>;
      case 'rejected':
        return <Badge variant="secondary" className="bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">Rejected</Badge>;
    }
  };

  const getStatusMessage = () => {
    switch (application.status) {
      case 'pending':
        return {
          title: 'Application Under Review',
          description: 'Your organization application is being reviewed by our team. We\'ll notify you via email once a decision has been made.',
          timeline: 'Expected review time: 2-3 business days'
        };
      case 'approved':
        return {
          title: 'Application Approved!',
          description: 'Congratulations! Your organization has been approved. You should receive an email with login credentials and next steps shortly.',
          timeline: 'Check your email for onboarding instructions'
        };
      case 'rejected':
        return {
          title: 'Application Not Approved',
          description: 'Unfortunately, your application was not approved at this time. Please review the feedback below and consider reapplying.',
          timeline: 'You can submit a new application addressing the concerns mentioned'
        };
    }
  };

  const statusInfo = getStatusMessage();

  return (
    <>
      <Head title="Application Status" />
      <div className="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-2xl w-full space-y-6">
          <div className="text-center space-y-2">
            <h1 className="text-2xl font-semibold tracking-tight">Application Status</h1>
            <p className="text-muted-foreground">
              Track the status of your organization application
            </p>
          </div>

          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  {getStatusIcon()}
                  <div>
                    <CardTitle className="text-lg">{application.organization_name}</CardTitle>
                    <CardDescription>Organization Application</CardDescription>
                  </div>
                </div>
                {getStatusBadge()}
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Reference Number */}
              <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                <div>
                  <p className="text-sm font-medium">Reference Number</p>
                  <p className="text-sm text-muted-foreground font-mono">{application.reference_number}</p>
                </div>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={copyReferenceNumber}
                  className="h-8 w-8 p-0"
                >
                  <Copy className="h-4 w-4" />
                </Button>
              </div>

              {/* Status Information */}
              <div className="space-y-3">
                <h3 className="text-sm font-medium">{statusInfo.title}</h3>
                <p className="text-sm text-muted-foreground">{statusInfo.description}</p>
                <p className="text-xs text-muted-foreground italic">{statusInfo.timeline}</p>
              </div>

              {/* Timeline */}
              <div className="space-y-3">
                <h4 className="text-sm font-medium">Timeline</h4>
                <div className="space-y-2">
                  <div className="flex items-center gap-3 text-sm">
                    <CheckCircle className="h-4 w-4 text-green-500" />
                    <span>Application submitted</span>
                    <span className="text-muted-foreground ml-auto">
                      {new Date(application.submitted_at).toLocaleDateString()}
                    </span>
                  </div>
                  
                  {application.reviewed_at && (
                    <div className="flex items-center gap-3 text-sm">
                      {application.status === 'approved' ? (
                        <CheckCircle className="h-4 w-4 text-green-500" />
                      ) : (
                        <XCircle className="h-4 w-4 text-red-500" />
                      )}
                      <span>Application reviewed</span>
                      <span className="text-muted-foreground ml-auto">
                        {new Date(application.reviewed_at).toLocaleDateString()}
                      </span>
                    </div>
                  )}
                </div>
              </div>

              {/* Rejection Reason */}
              {application.status === 'rejected' && application.rejection_reason && (
                <div className="p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
                  <h4 className="text-sm font-medium text-red-800 dark:text-red-400 mb-2">
                    Reason for Rejection
                  </h4>
                  <p className="text-sm text-red-700 dark:text-red-300">
                    {application.rejection_reason}
                  </p>
                </div>
              )}

              {/* Actions */}
              <div className="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                {application.status === 'rejected' && (
                  <Button asChild className="flex-1">
                    <TextLink href={route('tenant-application.create')}>
                      Submit New Application
                    </TextLink>
                  </Button>
                )}
                
                {application.status === 'approved' && (
                  <Button asChild className="flex-1">
                    <TextLink href={route('login')}>
                      <ExternalLink className="mr-2 h-4 w-4" />
                      Go to Login
                    </TextLink>
                  </Button>
                )}

                <Button variant="outline" asChild className="flex-1">
                  <TextLink href={route('home')}>
                    Back to Home
                  </TextLink>
                </Button>
              </div>

              {/* Help Text */}
              <div className="text-center pt-4 border-t">
                <p className="text-xs text-muted-foreground">
                  Questions about your application?{' '}
                  <a href="mailto:support@sannu-sannu.com" className="text-primary hover:underline">
                    Contact Support
                  </a>
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}