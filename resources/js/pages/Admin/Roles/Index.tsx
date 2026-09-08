import AdminLayout from '@/pages/Admin/Layout';
import { Head, Link, router } from '@inertiajs/react';
import React from 'react';
import { Shield, Users, Lock, ChevronRight, Plus, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface Role {
    id: number;
    name: string;
    users_count: number;
    permissions_count: number;
}

interface Props {
    roles: Role[];
}

export default function RolesIndex({ roles }: Props) {
    const handleDelete = (role: Role) => {
        if (window.confirm(`Are you sure you want to delete the ${role.name} role?\n\nThis cannot be undone.`)) {
            router.delete(`/admin/roles/${role.id}`);
        }
    };

    return (
        <AdminLayout title="System Roles">
            <Head title="Roles - Admin" />

            <div className="mb-6 flex justify-between items-center">
                <div>
                    <h2 className="text-lg font-medium text-slate-900">Roles & Permissions</h2>
                    <p className="text-sm text-slate-500">Manage access control and define what users can do.</p>
                </div>
                <Button asChild>
                    <Link href="/admin/roles/create">
                        <Plus className="h-4 w-4 mr-2" />
                        Create Role
                    </Link>
                </Button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {roles.map(role => (
                    <div key={role.id} className="bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col">
                        <div className="p-6 flex-1">
                            <div className="flex items-center justify-between mb-4">
                                <div className="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                                    <Shield className="h-5 w-5" />
                                </div>
                                {role.name === 'admin' && (
                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                        System Role
                                    </span>
                                )}
                            </div>
                            <h3 className="text-lg font-bold text-slate-900 capitalize mb-1">{role.name}</h3>
                            <p className="text-sm text-slate-500 mb-6">
                                {role.name === 'admin' 
                                    ? 'Full access to all system features and settings.' 
                                    : `Standard access control for ${role.name}s.`}
                            </p>

                            <div className="flex gap-6 mb-4">
                                <div className="flex items-center gap-2">
                                    <Users className="h-4 w-4 text-slate-400" />
                                    <div className="text-sm">
                                        <span className="font-semibold text-slate-700">{role.users_count}</span> <span className="text-slate-500">Users</span>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Lock className="h-4 w-4 text-slate-400" />
                                    <div className="text-sm">
                                        <span className="font-semibold text-slate-700">{role.permissions_count}</span> <span className="text-slate-500">Permissions</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="bg-slate-50 p-4 border-t border-slate-100 mt-auto flex gap-2">
                            <Button variant="outline" className="flex-1 justify-between" asChild>
                                <Link href={`/admin/roles/${role.id}/edit`}>
                                    Edit Permissions
                                    <ChevronRight className="h-4 w-4 ml-2" />
                                </Link>
                            </Button>
                            {role.name !== 'admin' && (
                                <Button variant="destructive" size="icon" onClick={() => handleDelete(role)} title="Delete Role">
                                    <Trash2 className="h-4 w-4" />
                                </Button>
                            )}
                        </div>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
