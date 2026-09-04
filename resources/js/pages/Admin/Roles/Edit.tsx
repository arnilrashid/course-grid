import AdminLayout from '@/pages/Admin/Layout';
import { Head, Link, useForm } from '@inertiajs/react';
import React from 'react';
import { Shield, ChevronLeft, Save, AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    role: Role;
    permissions: Permission[];
}

export default function RolesEdit({ role, permissions }: Props) {
    const { data, setData, post, processing, isDirty } = useForm({
        _method: 'PUT',
        permissions: role.permissions.map(p => p.name)
    });

    const handleToggle = (permissionName: string, checked: boolean) => {
        if (checked) {
            setData('permissions', [...data.permissions, permissionName]);
        } else {
            setData('permissions', data.permissions.filter(p => p !== permissionName));
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/roles/${role.id}`);
    };

    const isAdmin = role.name === 'admin';

    // Group permissions by prefix (e.g. "view_courses" -> group "courses")
    const groupedPermissions = permissions.reduce((acc, perm) => {
        const parts = perm.name.split('_');
        const group = parts.length > 1 ? parts[1] : 'general';
        if (!acc[group]) acc[group] = [];
        acc[group].push(perm);
        return acc;
    }, {} as Record<string, Permission[]>);

    return (
        <AdminLayout title={`Edit Role: ${role.name}`}>
            <Head title={`Edit Role ${role.name} - Admin`} />

            <div className="mb-6">
                <Link href="/admin/roles" className="text-sm font-medium text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <ChevronLeft className="h-4 w-4" />
                    Back to Roles
                </Link>
            </div>

            <form onSubmit={submit} className="max-w-4xl space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="h-12 w-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                            <Shield className="h-6 w-6" />
                        </div>
                        <div>
                            <h2 className="text-2xl font-bold text-slate-900 capitalize">{role.name} Role</h2>
                            <p className="text-sm text-slate-500">Select the permissions assigned to this role.</p>
                        </div>
                    </div>
                    
                    <Button type="submit" disabled={processing || !isDirty || isAdmin}>
                        <Save className="h-4 w-4 mr-2" />
                        Save Changes
                    </Button>
                </div>

                {isAdmin && (
                    <div className="p-4 bg-rose-50 border border-rose-200 rounded-lg flex gap-3 text-rose-800">
                        <AlertTriangle className="h-5 w-5 shrink-0" />
                        <div>
                            <p className="font-semibold mb-1">System Role Locked</p>
                            <p className="text-sm">The <strong>admin</strong> role is a protected system role. It automatically has access to all permissions and cannot be modified to prevent accidental system lockout.</p>
                        </div>
                    </div>
                )}

                <Card className={isAdmin ? 'opacity-60 pointer-events-none' : ''}>
                    <CardHeader>
                        <CardTitle>Permissions Configuration</CardTitle>
                        <CardDescription>Grant specific capabilities to users with this role.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-8">
                        {Object.entries(groupedPermissions).map(([groupName, perms]) => (
                            <div key={groupName}>
                                <h4 className="text-sm font-semibold text-slate-900 capitalize mb-4 border-b pb-2">
                                    {groupName} Management
                                </h4>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {perms.map(perm => {
                                        const isChecked = data.permissions.includes(perm.name);
                                        return (
                                            <div key={perm.id} className="flex items-start space-x-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors">
                                                <Checkbox 
                                                    id={`perm-${perm.id}`} 
                                                    checked={isChecked}
                                                    onCheckedChange={(checked) => handleToggle(perm.name, checked as boolean)}
                                                />
                                                <div className="space-y-1 leading-none">
                                                    <label 
                                                        htmlFor={`perm-${perm.id}`} 
                                                        className="text-sm font-medium leading-none cursor-pointer text-slate-700"
                                                    >
                                                        {perm.name.replace(/_/g, ' ')}
                                                    </label>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </form>
        </AdminLayout>
    );
}
