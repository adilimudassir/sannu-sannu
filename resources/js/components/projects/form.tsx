import InputError from '@/components/input-error';
import ProductManager from '@/components/projects/product-manager';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { formatDateForInput } from '@/lib/date-utils';
import type { Product, Project, Tenant } from '@/types';
import { useForm } from '@inertiajs/react';
import * as React from 'react';

interface ProjectFormProps {
    project?: Project;
    tenant?: Tenant;
    tenants?: Tenant[];
    isAdmin?: boolean;
    onSubmit: (data: ProjectFormData) => void;
    processing?: boolean;
    errors?: Record<string, string>;
}

interface ProjectFormData {
    name: string;
    description: string;
    visibility: 'public' | 'private' | 'invite_only';
    requires_approval: boolean;
    max_contributors?: number;
    total_amount: number;
    minimum_contribution?: number;
    payment_options: string[];
    installment_frequency?: 'monthly' | 'quarterly' | 'custom';
    custom_installment_months?: number;
    start_date: string;
    end_date: string;
    registration_deadline?: string;
    managed_by?: number[];
    settings?: Record<string, any>;
    products: Product[];
    tenant_id?: number;
    [key: string]: any; // Index signature to satisfy FormDataType constraint
}

export default function ProjectForm({ project, tenant, tenants = [], isAdmin = false, onSubmit, processing = false, errors = {} }: ProjectFormProps) {
    const { data, setData, transform } = useForm({
        name: project?.name || '',
        description: project?.description || '',
        visibility: project?.visibility || 'public',
        requires_approval: project?.requires_approval || false,
        max_contributors: project?.max_contributors || undefined,
        total_amount: project?.total_amount || 0,
        minimum_contribution: project?.minimum_contribution || undefined,
        payment_options: project?.payment_options || ['full'],
        installment_frequency: project?.installment_frequency || 'monthly',
        custom_installment_months: project?.custom_installment_months || undefined,
        start_date: formatDateForInput(project?.start_date),
        end_date: formatDateForInput(project?.end_date),
        registration_deadline: formatDateForInput(project?.registration_deadline),
        managed_by: project?.managed_by || [],
        settings: project?.settings || {},
        products: project?.products || [{ name: '', description: '', price: 0, sort_order: 0 }],
        tenant_id: project?.tenant?.id || tenant?.id || undefined,
    });

    // Calculate total from products
    React.useEffect(() => {
        const total = data.products.reduce((sum, product) => sum + (product.price || 0), 0);
        if (total !== data.total_amount) {
            setData('total_amount', total);
        }
    }, [data.products]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        transform((data) => ({
            ...data,
            products: data.products.map((product, index) => ({
                ...product,
                sort_order: index,
            })),
        }));

        onSubmit(data);
    };

    const visibilityOptions = [
        { value: 'public', label: 'Public', description: 'Anyone can view and join this project' },
        { value: 'private', label: 'Private', description: 'Only tenant members can view and join' },
        { value: 'invite_only', label: 'Invite Only', description: 'Only invited users can participate' },
    ];

    const paymentOptions = [
        { value: 'full', label: 'Full Payment' },
        { value: 'installments', label: 'Installments' },
    ];

    const installmentFrequencies = [
        { value: 'monthly', label: 'Monthly' },
        { value: 'quarterly', label: 'Quarterly' },
        { value: 'custom', label: 'Custom' },
    ];

    return (
        <form onSubmit={handleSubmit} className="space-y-8">
            {/* Basic Information */}
            <Card>
                <CardHeader>
                    <CardTitle>Basic Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                    {isAdmin && tenants.length > 0 && (
                        <div className="space-y-2">
                            <Label htmlFor="tenant_id" data-required>
                                Organization
                            </Label>
                            <Select value={data.tenant_id?.toString()} onValueChange={(value) => setData('tenant_id', parseInt(value))}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select organization" />
                                </SelectTrigger>
                                <SelectContent>
                                    {tenants.map((t) => (
                                        <SelectItem key={t.id} value={t.id.toString()}>
                                            {t.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.tenant_id} />
                        </div>
                    )}

                    <div className="space-y-2">
                        <Label htmlFor="name" data-required>
                            Project Name
                        </Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Enter project name"
                            aria-invalid={!!errors.name}
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="description" data-required>
                            Description
                        </Label>
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Describe your project and its goals"
                            rows={4}
                            aria-invalid={!!errors.description}
                        />
                        <InputError message={errors.description} />
                    </div>
                </CardContent>
            </Card>

            {/* Project Settings */}
            <Card>
                <CardHeader>
                    <CardTitle>Project Settings</CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="visibility" data-required>
                                Visibility
                            </Label>
                            <Select
                                value={data.visibility}
                                onValueChange={(value: 'public' | 'private' | 'invite_only') => setData('visibility', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select visibility">
                                        {data.visibility && (
                                            <span className="flex items-center">
                                                {visibilityOptions.find((opt) => opt.value === data.visibility)?.label}
                                            </span>
                                        )}
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    {visibilityOptions.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            <div className="flex flex-col">
                                                <span className="font-medium">{option.label}</span>
                                                <span className="text-xs text-muted-foreground">{option.description}</span>
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.visibility} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="max_contributors">Maximum Contributors (optional)</Label>
                            <Input
                                id="max_contributors"
                                type="number"
                                min="1"
                                value={data.max_contributors || ''}
                                onChange={(e) => setData('max_contributors', e.target.value ? parseInt(e.target.value) : undefined)}
                                placeholder="Leave empty for unlimited"
                            />
                            <InputError message={errors.max_contributors} />
                        </div>
                    </div>

                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id="requires_approval"
                            checked={data.requires_approval}
                            onCheckedChange={(checked) => setData('requires_approval', !!checked)}
                        />
                        <Label htmlFor="requires_approval">Require approval for new contributors</Label>
                    </div>
                </CardContent>
            </Card>

            {/* Financial Settings */}
            <Card>
                <CardHeader>
                    <CardTitle>Financial Settings</CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="space-y-2">
                        <Label htmlFor="minimum_contribution">Minimum Contribution (optional)</Label>
                        <Input
                            id="minimum_contribution"
                            type="number"
                            min="0.01"
                            step="0.01"
                            value={data.minimum_contribution || ''}
                            onChange={(e) => setData('minimum_contribution', e.target.value ? parseFloat(e.target.value) : undefined)}
                            placeholder="Enter amount in Naira"
                            className="max-w-xs"
                        />
                        <InputError message={errors.minimum_contribution} />
                    </div>

                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label>Payment Options</Label>
                            <div className="flex flex-wrap gap-4">
                                {paymentOptions.map((option) => (
                                    <div key={option.value} className="flex items-center space-x-2">
                                        <Checkbox
                                            id={`payment_${option.value}`}
                                            checked={data.payment_options.includes(option.value)}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    setData('payment_options', [...data.payment_options, option.value]);
                                                } else {
                                                    setData(
                                                        'payment_options',
                                                        data.payment_options.filter((p) => p !== option.value),
                                                    );
                                                }
                                            }}
                                        />
                                        <Label htmlFor={`payment_${option.value}`}>{option.label}</Label>
                                    </div>
                                ))}
                            </div>
                            <InputError message={errors.payment_options} />
                        </div>

                        {data.payment_options.includes('installments') && (
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="installment_frequency">Installment Frequency</Label>
                                    <Select
                                        value={data.installment_frequency}
                                        onValueChange={(value: 'monthly' | 'quarterly' | 'custom') => setData('installment_frequency', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select frequency" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {installmentFrequencies.map((freq) => (
                                                <SelectItem key={freq.value} value={freq.value}>
                                                    {freq.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.installment_frequency} />
                                </div>

                                {data.installment_frequency === 'custom' && (
                                    <div className="space-y-2">
                                        <Label htmlFor="custom_installment_months">Custom Installment Months</Label>
                                        <Input
                                            id="custom_installment_months"
                                            type="number"
                                            min="2"
                                            max="60"
                                            value={data.custom_installment_months || ''}
                                            onChange={(e) =>
                                                setData('custom_installment_months', e.target.value ? parseInt(e.target.value) : undefined)
                                            }
                                            placeholder="Number of months"
                                        />
                                        <InputError message={errors.custom_installment_months} />
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Timeline */}
            <Card>
                <CardHeader>
                    <CardTitle>Timeline</CardTitle>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div className="space-y-2">
                            <Label htmlFor="start_date" data-required>
                                Start Date
                            </Label>
                            <Input
                                id="start_date"
                                type="date"
                                value={data.start_date}
                                onChange={(e) => setData('start_date', e.target.value)}
                                aria-invalid={!!errors.start_date}
                            />
                            <InputError message={errors.start_date} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="end_date" data-required>
                                End Date
                            </Label>
                            <Input
                                id="end_date"
                                type="date"
                                value={data.end_date}
                                onChange={(e) => setData('end_date', e.target.value)}
                                aria-invalid={!!errors.end_date}
                            />
                            <InputError message={errors.end_date} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="registration_deadline">Registration Deadline (optional)</Label>
                            <Input
                                id="registration_deadline"
                                type="date"
                                value={data.registration_deadline || ''}
                                onChange={(e) => setData('registration_deadline', e.target.value)}
                            />
                            <InputError message={errors.registration_deadline} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Products */}
            <Card>
                <CardHeader>
                    <CardTitle>Products</CardTitle>
                </CardHeader>
                <CardContent>
                    <ProductManager products={data.products} onChange={(products) => setData('products', products)} errors={errors} />
                    <InputError message={errors.total_amount} />
                </CardContent>
            </Card>

            {/* Submit Button */}
            <Card>
                <CardContent className="pt-6">
                    <div className="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div className="text-sm text-muted-foreground">
                            {project ? 'Update your project details and save changes.' : 'Review all information before creating your project.'}
                        </div>
                        <Button type="submit" disabled={processing} className="w-full min-w-32 sm:w-auto">
                            {processing ? 'Saving...' : project ? 'Update Project' : 'Create Project'}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    );
}
