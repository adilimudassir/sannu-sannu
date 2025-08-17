import React from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, usePage, router } from '@inertiajs/react';

export default function TenantApplicationShow() {
    const { application } = usePage().props as any;

    const [notes, setNotes] = React.useState('');
    const [rejectionReason, setRejectionReason] = React.useState('');
    const [submitting, setSubmitting] = React.useState(false);
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

    return (
        <AppLayout>
            <Head title={`Review Application: ${application.organization_name}`} />
            <div className="space-y-6">
                <h1 className="text-2xl font-bold">Review Tenant Application</h1>
                <div className="bg-card p-6 rounded shadow">
                    <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt className="font-semibold">Organization Name</dt>
                            <dd>{application.organization_name}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Reference Number</dt>
                            <dd>{application.reference_number}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Contact Person</dt>
                            <dd>{application.contact_person_name} ({application.contact_person_email})</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Phone</dt>
                            <dd>{application.contact_person_phone}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Business Registration #</dt>
                            <dd>{application.business_registration_number}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Industry</dt>
                            <dd>{application.industry_type}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Website</dt>
                            <dd>{application.website_url}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Status</dt>
                            <dd>{application.status}</dd>
                        </div>
                        <div>
                            <dt className="font-semibold">Submitted At</dt>
                            <dd>{application.submitted_at}</dd>
                        </div>
                        {application.reviewed_at && (
                            <div>
                                <dt className="font-semibold">Reviewed At</dt>
                                <dd>{application.reviewed_at}</dd>
                            </div>
                        )}
                        {application.reviewer_id && (
                            <div>
                                <dt className="font-semibold">Reviewed By</dt>
                                <dd>{application.reviewer_id}</dd>
                            </div>
                        )}
                        {application.rejection_reason && (
                            <div>
                                <dt className="font-semibold">Rejection Reason</dt>
                                <dd>{application.rejection_reason}</dd>
                            </div>
                        )}
                        {application.notes && (
                            <div className="md:col-span-2">
                                <dt className="font-semibold">Notes</dt>
                                <dd>{application.notes}</dd>
                            </div>
                        )}
                    </dl>
                </div>

                {canReview && (
                    <div className="mt-8 flex flex-col md:flex-row gap-6">
                        <form onSubmit={handleApprove} className="flex-1 bg-green-50 p-4 rounded shadow space-y-2">
                            <h2 className="font-semibold text-green-700">Approve Application</h2>
                            <label className="block">
                                <span className="text-sm">Notes (optional)</span>
                                <textarea className="input input-bordered w-full" value={notes} onChange={e => setNotes(e.target.value)} />
                            </label>
                            <button type="submit" className="btn btn-success" disabled={submitting}>Approve</button>
                        </form>
                        <form onSubmit={handleReject} className="flex-1 bg-red-50 p-4 rounded shadow space-y-2">
                            <h2 className="font-semibold text-red-700">Reject Application</h2>
                            <label className="block">
                                <span className="text-sm">Rejection Reason</span>
                                <input className="input input-bordered w-full" value={rejectionReason} onChange={e => setRejectionReason(e.target.value)} required />
                            </label>
                            <label className="block">
                                <span className="text-sm">Notes (optional)</span>
                                <textarea className="input input-bordered w-full" value={notes} onChange={e => setNotes(e.target.value)} />
                            </label>
                            <button type="submit" className="btn btn-danger" disabled={submitting}>Reject</button>
                        </form>
                    </div>
                )}

                {status === 'approved' && (
                    <div className="mt-8 p-4 bg-green-100 rounded text-green-800 font-semibold">This application has been approved.</div>
                )}
                {status === 'rejected' && (
                    <div className="mt-8 p-4 bg-red-100 rounded text-red-800 font-semibold">This application has been rejected.</div>
                )}
            </div>
        </AppLayout>
    );
}
