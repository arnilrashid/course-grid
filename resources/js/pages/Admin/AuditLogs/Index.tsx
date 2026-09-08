import AdminLayout from '@/pages/Admin/Layout';
import { Head, router, Link } from '@inertiajs/react';
import React, { useState } from 'react';
import { Search, FileText, ArrowRight } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';

interface User {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
}

interface AuditLog {
    id: number;
    user_id: number;
    action: string;
    auditable_type: string;
    auditable_id: number;
    old_values: any;
    new_values: any;
    created_at: string;
    user?: User;
}

interface Props {
    logs: {
        data: AuditLog[];
        links: any[];
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search: string;
    };
}

export default function AuditLogsIndex({ logs, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleFilter = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/admin/audit-logs', { 
            search 
        }, { preserveState: true });
    };

    const formatValue = (val: any) => {
        if (typeof val === 'object' && val !== null) {
            return JSON.stringify(val, null, 2);
        }
        return String(val);
    };

    return (
        <AdminLayout title="Audit Logs">
            <Head title="Audit Logs - Admin" />

            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div className="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <form onSubmit={handleFilter} className="relative w-full sm:w-64">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                        <Input
                            type="text"
                            placeholder="Search logs..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9 bg-white"
                        />
                    </form>
                </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th className="px-6 py-4 font-semibold text-slate-600">Date</th>
                                <th className="px-6 py-4 font-semibold text-slate-600">User</th>
                                <th className="px-6 py-4 font-semibold text-slate-600">Action</th>
                                <th className="px-6 py-4 font-semibold text-slate-600">Details</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {logs.data.map((log) => (
                                <tr key={log.id} className="hover:bg-slate-50/50 transition-colors">
                                    <td className="px-6 py-4 text-slate-500 whitespace-nowrap align-top">
                                        {new Date(log.created_at).toLocaleString()}
                                    </td>
                                    <td className="px-6 py-4 align-top">
                                        {log.user ? (
                                            <div>
                                                <div className="font-medium text-slate-900">{log.user.name}</div>
                                                <div className="text-slate-500 text-xs">{log.user.email}</div>
                                            </div>
                                        ) : (
                                            <span className="text-slate-400 italic">System / Unknown</span>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 align-top whitespace-nowrap">
                                        <Badge variant="outline" className="bg-slate-50 text-slate-700">
                                            {log.action}
                                        </Badge>
                                        <div className="mt-2 text-xs text-slate-400">
                                            Target: {log.auditable_type?.split('\\').pop()} #{log.auditable_id}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4">
                                        {(log.old_values || log.new_values) ? (
                                            <div className="space-y-3">
                                                {log.old_values && Object.keys(log.old_values).length > 0 && (
                                                    <div className="bg-red-50/50 border border-red-100 rounded p-3 text-xs overflow-x-auto">
                                                        <div className="font-semibold text-red-700 mb-1 flex items-center gap-1">
                                                            <ArrowRight className="h-3 w-3 rotate-180" />
                                                            From
                                                        </div>
                                                        <pre className="text-red-900/80 m-0">
                                                            {formatValue(log.old_values)}
                                                        </pre>
                                                    </div>
                                                )}
                                                {log.new_values && Object.keys(log.new_values).length > 0 && (
                                                    <div className="bg-emerald-50/50 border border-emerald-100 rounded p-3 text-xs overflow-x-auto">
                                                        <div className="font-semibold text-emerald-700 mb-1 flex items-center gap-1">
                                                            <ArrowRight className="h-3 w-3" />
                                                            To
                                                        </div>
                                                        <pre className="text-emerald-900/80 m-0">
                                                            {formatValue(log.new_values)}
                                                        </pre>
                                                    </div>
                                                )}
                                            </div>
                                        ) : (
                                            <span className="text-slate-400 text-xs italic">No payload details</span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {logs.data.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-6 py-12 text-center text-slate-500">
                                        <div className="flex flex-col items-center justify-center">
                                            <FileText className="h-10 w-10 text-slate-300 mb-3" />
                                            <p>No audit logs found.</p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50">
                    <div className="text-sm text-slate-500">
                        Showing <span className="font-medium text-slate-900">{logs.from || 0}</span> to <span className="font-medium text-slate-900">{logs.to || 0}</span> of <span className="font-medium text-slate-900">{logs.total}</span> logs
                    </div>
                    <div className="flex gap-1">
                        {logs.links.map((link, i) => (
                            link.url ? (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`px-3 py-1.5 rounded-md border text-sm font-medium transition-colors ${
                                        link.active 
                                            ? 'bg-indigo-600 border-indigo-600 text-white' 
                                            : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={i}
                                    className="px-3 py-1.5 rounded-md border border-slate-100 bg-slate-50 text-slate-400 text-sm font-medium cursor-not-allowed"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
