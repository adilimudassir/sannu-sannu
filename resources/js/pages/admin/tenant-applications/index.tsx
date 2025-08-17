import AppLayout from '@/layouts/app-layout';
import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';

export default function TenantApplicationsList() {
    const { applications, filters } = usePage().props as any;
    const [search, setSearch] = useState(filters?.search || '');
    const [status, setStatus] = useState(filters?.status || '');
    const [sort, setSort] = useState(filters?.sort || 'submitted_at');
    const [direction, setDirection] = useState(filters?.direction || 'desc');

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/tenant-applications', {
            search,
            status,
            sort,
            direction,
        }, { preserveState: true });
    };

    return (
        <AppLayout>
            <Head title="Tenant Applications" />
            <div className="space-y-6">
                <h1 className="text-2xl font-bold">Tenant Applications</h1>
                <p className="text-muted-foreground mb-4">Review and manage all pending, approved, and rejected tenant applications.</p>
                <form onSubmit={handleFilter} className="flex flex-wrap gap-2 mb-4 items-end">
                    <input
                        type="text"
                        placeholder="Search by org, email, ref, etc."
                        className="input input-bordered"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                    />
                    <select className="input input-bordered" value={status} onChange={e => setStatus(e.target.value)}>
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select className="input input-bordered" value={sort} onChange={e => setSort(e.target.value)}>
                        <option value="submitted_at">Submitted</option>
                        <option value="organization_name">Organization</option>
                    </select>
                    <select className="input input-bordered" value={direction} onChange={e => setDirection(e.target.value)}>
                        <option value="desc">Desc</option>
                        <option value="asc">Asc</option>
                    </select>
                    <button type="submit" className="btn btn-primary">Filter</button>
                </form>
                <div className="overflow-x-auto">
                    <table className="min-w-full border rounded-lg">
                        <thead>
                            <tr className="bg-muted">
                                <th className="p-2 text-left">Ref #</th>
                                <th className="p-2 text-left">Organization</th>
                                <th className="p-2 text-left">Contact</th>
                                <th className="p-2 text-left">Status</th>
                                <th className="p-2 text-left">Submitted</th>
                                <th className="p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {applications.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="text-center p-8 text-muted-foreground">No applications found.</td>
                                </tr>
                            ) : (
                                applications.data.map((app: any) => (
                                    <tr key={app.id} className="border-b">
                                        <td className="p-2 font-mono">{app.reference_number}</td>
                                        <td className="p-2">{app.organization_name}</td>
                                        <td className="p-2">
                                            <div>{app.contact_person_name}</div>
                                            <div className="text-xs text-muted-foreground">{app.contact_person_email}</div>
                                        </td>
                                        <td className="p-2 capitalize">
                                            <span className={`badge badge-${app.status}`}>{app.status}</span>
                                        </td>
                                        <td className="p-2">{app.submitted_at ? new Date(app.submitted_at).toLocaleDateString() : ''}</td>
                                        <td className="p-2">
                                            <button
                                                className="btn btn-xs btn-outline"
                                                onClick={() => router.visit(`/admin/tenant-applications/${app.id}`)}
                                            >
                                                Review
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
                {/* Pagination */}
                {applications.links && (
                    <div className="mt-4 flex gap-2 flex-wrap">
                        {applications.links.map((link: any, i: number) => (
                            <button
                                key={i}
                                className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-ghost'}`}
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url, filters, { preserveState: true })}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
