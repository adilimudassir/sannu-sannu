import React from 'react';
import { Head } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { AuthCard } from '@/components/auth/auth-card';
import { AuthForm, AuthFormField } from '@/components/auth/auth-form';
import { AuthInput } from '@/components/auth/auth-input';
import { AuthButton } from '@/components/auth/auth-button';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

export interface TenantApplicationData {
  organization_name: string;
  business_description: string;
  industry_type: string;
  contact_person_name: string;
  contact_person_email: string;
  contact_person_phone: string;
  business_registration_number: string;
  website_url: string;
}

interface TenantApplicationFormProps {
  onSubmit: (data: TenantApplicationData, form: any) => void;
  onBack: () => void;
  initialData?: Partial<TenantApplicationData>;
  industryTypes: Record<string, string>;
}

export const TenantApplicationForm: React.FC<TenantApplicationFormProps> = ({ 
  onSubmit, 
  onBack, 
  initialData = {}, 
  industryTypes 
}) => {
  const handleSubmit = (data: TenantApplicationData, form: any) => {
    onSubmit(data, form);
  };

  return (
    <>
      <Head title="Organization Registration" />
      <AuthCard 
        title="Register Your Organization" 
        description="Tell us about your organization to get started with creating contribution-based projects"
        size="2xl"
      >
        <div className="mb-6">
          <Button
            variant="ghost"
            size="sm"
            onClick={onBack}
            className="p-0 h-auto font-normal text-muted-foreground hover:text-foreground"
          >
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back
          </Button>
        </div>

        <AuthForm
          initialData={{
            organization_name: '',
            business_description: '',
            industry_type: '',
            contact_person_name: '',
            contact_person_email: '',
            contact_person_phone: '',
            business_registration_number: '',
            website_url: '',
            ...initialData,
          }}
          onSubmit={handleSubmit}
        >
          {(form) => (
            <>
              <div className="grid gap-8">
                {/* Organization Information */}
                <div className="space-y-6">
                  <div className="border-b pb-2">
                    <h3 className="text-sm font-medium text-foreground">Organization Information</h3>
                    <p className="text-xs text-muted-foreground">Basic details about your organization</p>
                  </div>

                  {/* Organization Name and Industry - Side by Side */}
                  <div className="grid gap-6 md:grid-cols-2">
                    <AuthFormField
                      label="Organization Name"
                      name="organization_name"
                      error={form.errors.organization_name}
                      required
                    >
                      <AuthInput
                        id="organization_name"
                        name="organization_name"
                        type="text"
                        required
                        autoFocus
                        tabIndex={1}
                        value={form.data.organization_name}
                        onChange={(e) => form.setData('organization_name', e.target.value)}
                        placeholder="Your Organization Name"
                        error={!!form.errors.organization_name}
                        aria-describedby={form.errors.organization_name ? "organization-name-error" : undefined}
                      />
                    </AuthFormField>

                    <AuthFormField
                      label="Industry Type"
                      name="industry_type"
                      error={form.errors.industry_type}
                      required
                    >
                      <Select
                        value={form.data.industry_type}
                        onValueChange={(value) => form.setData('industry_type', value)}
                      >
                        <SelectTrigger tabIndex={2}>
                          <SelectValue placeholder="Select your industry" />
                        </SelectTrigger>
                        <SelectContent>
                          {Object.entries(industryTypes).map(([value, label]) => (
                            <SelectItem key={value} value={value}>
                              {label}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </AuthFormField>
                  </div>

                  {/* Business Description - Full Width */}
                  <div className="w-full">
                    <AuthFormField
                      label="Business Description"
                      name="business_description"
                      error={form.errors.business_description}
                      required
                    >
                      <Textarea
                        id="business_description"
                        name="business_description"
                        required
                        tabIndex={3}
                        value={form.data.business_description}
                        onChange={(e) => form.setData('business_description', e.target.value)}
                        placeholder="Describe your business, what you do, and what types of projects you plan to create..."
                        className="min-h-[150px] resize-none"
                        maxLength={1000}
                      />
                      <div className={`text-xs mt-1 ${
                        form.data.business_description.length < 50 
                          ? 'text-destructive' 
                          : form.data.business_description.length > 900 
                            ? 'text-warning' 
                            : 'text-muted-foreground'
                      }`}>
                        {form.data.business_description.length}/1000 characters 
                        {form.data.business_description.length < 50 && (
                          <span className="ml-1">(minimum 50 required)</span>
                        )}
                      </div>
                    </AuthFormField>
                  </div>

                  {/* Additional Business Details - Side by Side */}
                  <div className="grid gap-6 md:grid-cols-2">
                    <AuthFormField
                      label="Business Registration Number"
                      name="business_registration_number"
                      error={form.errors.business_registration_number}
                    >
                      <AuthInput
                        id="business_registration_number"
                        name="business_registration_number"
                        type="text"
                        tabIndex={4}
                        value={form.data.business_registration_number}
                        onChange={(e) => form.setData('business_registration_number', e.target.value)}
                        placeholder="Optional - Registration number"
                        error={!!form.errors.business_registration_number}
                      />
                    </AuthFormField>

                    <AuthFormField
                      label="Website URL"
                      name="website_url"
                      error={form.errors.website_url}
                    >
                      <AuthInput
                        id="website_url"
                        name="website_url"
                        type="url"
                        tabIndex={5}
                        value={form.data.website_url}
                        onChange={(e) => form.setData('website_url', e.target.value)}
                        placeholder="https://your-website.com"
                        error={!!form.errors.website_url}
                      />
                    </AuthFormField>
                  </div>
                </div>

                {/* Contact Information */}
                <div className="space-y-4">
                  <div className="border-b pb-2">
                    <h3 className="text-sm font-medium text-foreground">Primary Contact Information</h3>
                    <p className="text-xs text-muted-foreground">Details for the main contact person</p>
                  </div>

                  <div className="grid gap-6 lg:grid-cols-3">
                    {/* Column 1: Contact Name */}
                    <AuthFormField
                      label="Contact Person Name"
                      name="contact_person_name"
                      error={form.errors.contact_person_name}
                      required
                    >
                      <AuthInput
                        id="contact_person_name"
                        name="contact_person_name"
                        type="text"
                        required
                        tabIndex={6}
                        autoComplete="name"
                        value={form.data.contact_person_name}
                        onChange={(e) => form.setData('contact_person_name', e.target.value)}
                        placeholder="Full name of primary contact"
                        error={!!form.errors.contact_person_name}
                      />
                    </AuthFormField>

                    {/* Column 2: Contact Email */}
                    <AuthFormField
                      label="Contact Email Address"
                      name="contact_person_email"
                      error={form.errors.contact_person_email}
                      required
                    >
                      <AuthInput
                        id="contact_person_email"
                        name="contact_person_email"
                        type="email"
                        required
                        tabIndex={7}
                        autoComplete="email"
                        value={form.data.contact_person_email}
                        onChange={(e) => form.setData('contact_person_email', e.target.value)}
                        placeholder="contact@organization.com"
                        error={!!form.errors.contact_person_email}
                      />
                    </AuthFormField>

                    {/* Column 3: Contact Phone */}
                    <AuthFormField
                      label="Contact Phone Number"
                      name="contact_person_phone"
                      error={form.errors.contact_person_phone}
                    >
                      <AuthInput
                        id="contact_person_phone"
                        name="contact_person_phone"
                        type="tel"
                        tabIndex={8}
                        autoComplete="tel"
                        value={form.data.contact_person_phone}
                        onChange={(e) => form.setData('contact_person_phone', e.target.value)}
                        placeholder="Optional - Phone number"
                        error={!!form.errors.contact_person_phone}
                      />
                    </AuthFormField>
                  </div>
                </div>
              </div>

              <div className="bg-muted/50 p-4 rounded-lg">
                <h4 className="text-sm font-medium mb-2">What happens next?</h4>
                <ul className="text-xs text-muted-foreground space-y-1">
                  <li>• Your application will be reviewed by our team within 2-3 business days</li>
                  <li>• You'll receive a confirmation email with your unique application reference number</li>
                  <li>• We may contact you if additional information is needed</li>
                  <li>• You'll be notified by email about the approval decision</li>
                  <li>• If approved, you'll receive login credentials and comprehensive onboarding instructions</li>
                  <li>• You can track your application status using the reference number provided</li>
                </ul>
              </div>

              <AuthButton
                type="submit"
                className="w-full"
                tabIndex={9}
                loading={form.processing}
                loadingText="Submitting application..."
                aria-describedby={form.hasErrors ? "form-errors" : undefined}
              >
                Submit Organization Application
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
};