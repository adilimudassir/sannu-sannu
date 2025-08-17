import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Users, Building2, ArrowRight, CheckCircle } from 'lucide-react';

interface RegistrationTypeSelectionProps {
  onSelectType: (type: 'contributor' | 'organization') => void;
}

export const RegistrationTypeSelection: React.FC<RegistrationTypeSelectionProps> = ({ onSelectType }) => {
  return (
    <div className="space-y-6">
      <div className="text-center space-y-2">
        <h1 className="text-2xl font-semibold tracking-tight">Join Sannu-Sannu</h1>
        <p className="text-muted-foreground">
          Choose how you'd like to participate in our contribution-based platform
        </p>
      </div>

      <div className="grid gap-4 md:grid-cols-2">
        {/* Contributor Option */}
        <Card className="relative cursor-pointer transition-all hover:shadow-md hover:border-primary/50">
          <CardHeader className="pb-4">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                <Users className="h-5 w-5 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <CardTitle className="text-lg">Join as Contributor</CardTitle>
                <Badge variant="secondary" className="text-xs">
                  Immediate Access
                </Badge>
              </div>
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            <CardDescription className="text-sm leading-relaxed">
              Perfect for individuals who want to contribute to projects and earn from their contributions.
            </CardDescription>
            
            <div className="space-y-2">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Browse and join public projects</span>
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Earn from your contributions</span>
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Access to contributor dashboard</span>
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Instant account activation</span>
              </div>
            </div>

            <Button 
              onClick={() => onSelectType('contributor')}
              className="w-full"
              variant="default"
            >
              Get Started as Contributor
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </CardContent>
        </Card>

        {/* Organization Option */}
        <Card className="relative cursor-pointer transition-all hover:shadow-md hover:border-primary/50">
          <CardHeader className="pb-4">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                <Building2 className="h-5 w-5 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <CardTitle className="text-lg">Register Organization</CardTitle>
                <Badge variant="outline" className="text-xs">
                  Approval Required
                </Badge>
              </div>
            </div>
          </CardHeader>
          <CardContent className="space-y-4">
            <CardDescription className="text-sm leading-relaxed">
              For organizations that want to create and manage contribution-based projects with their own branding.
            </CardDescription>
            
            <div className="space-y-2">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Create and manage projects</span>
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Custom organization branding</span>
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Invite team members</span>
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>Advanced analytics and reporting</span>
              </div>
            </div>

            <div className="p-3 bg-muted/50 rounded-lg">
              <p className="text-xs text-muted-foreground">
                <strong>Note:</strong> Organization applications require approval. 
                You'll receive a confirmation email and be notified once your application is reviewed.
              </p>
            </div>

            <Button 
              onClick={() => onSelectType('organization')}
              className="w-full"
              variant="outline"
            >
              Apply for Organization
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </CardContent>
        </Card>
      </div>

      <div className="text-center">
        <p className="text-sm text-muted-foreground">
          Need help choosing? Check our{' '}
          <a href="#" className="text-primary hover:underline">
            comparison guide
          </a>{' '}
          or{' '}
          <a href="#" className="text-primary hover:underline">
            contact support
          </a>
        </p>
      </div>
    </div>
  );
};