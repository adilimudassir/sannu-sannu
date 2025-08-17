import React from 'react';
import { router } from '@inertiajs/react';
import { TenantApplicationForm, TenantApplicationData } from '@/components/auth/tenant-application-form';

interface TenantApplicationPageProps {
  industryTypes: Record<string, string>;
}

export default function TenantApplication({ industryTypes }: TenantApplicationPageProps) {
  const handleSubmit = (data: TenantApplicationData, form: any) => {
    form.post(route('tenant-application.store'), {
      onSuccess: () => {
        // The controller will redirect to the status page
      },
      onFinish: () => {
        // Don't reset form data on error so user doesn't lose their input
      },
    });
  };

  const handleBack = () => {
    router.visit(route('register'));
  };

  return (
    <div className="min-h-screen flex items-center justify-center py-5 px-4 sm:px-6 lg:px-8">
      <div className="w-full">
        <TenantApplicationForm
          onSubmit={handleSubmit}
          onBack={handleBack}
          industryTypes={industryTypes}
        />
      </div>
    </div>
  );
}