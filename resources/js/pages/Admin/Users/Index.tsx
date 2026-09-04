import AdminLayout from '@/pages/Admin/Layout';
import { Head, router, Link } from '@inertiajs/react';
import React, { useState } from 'react';
import { Search, Filter, Shield, MoreHorizontal, User as UserIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    suspended_at: string | null;
    created_at: string;
    roles: Role[];
}

interface Props {
    users: {
        data: User[];
        links: any[];
        total: number;
        from: number;
        to: number;
    };
    roles: Role[];
    filters: {
        search: string;
        role: string;
    };
}

export default function UsersIndex({ users, roles, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || 'all');

    const handleFilter = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        router.get('/admin/users', { 
            search, 
            role: roleFilter === 'all' ? undefined : roleFilter 
        }, { preserveState: true });
    };

    const handleRoleChange = (value: string) => {
        setRoleFilter(value);
        router.get('/admin/users', { 
            search, 
            role: value === 'all' ? undefined : value 
        }, { preserveState: true });
    };

    const toggleSuspension = (user: User) => {
        const action = user.suspended_at ? 'unsuspend' : 'suspend';
        
        router.post(`/admin/users/${user.id}/${action}`, { reason: '' }, {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title="Users & Roles">
            <Head title="Users - Admin" />

            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div className="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <form onSubmit={handleFilter} className="relative w-full sm:w-64">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                        <Input
                            type="text"
                            placeholder="Search users..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9 bg-white"
                        />
                    </form>
                    
                    <Select value={roleFilter} onValueChange={handleRoleChange}>
                        <SelectTrigger className="w-full sm:w-48 bg-white">
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-slate-400" />
                                <SelectValue placeholder="All Roles" />
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Roles</SelectItem>
                            {roles.map(role => (
                                <SelectItem key={role.id} value={role.name}>
                                    {role.name.charAt(0).toUpperCase() + role.name.slice(1)}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link href="/admin/roles">
                            <Shield className="h-4 w-4 mr-2" />
                            Manage Roles
                        </Link>
                    </Button>
                </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm whitespace-nowrap">
                        <thead className="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th className="px-6 py-4 font-semibold text-slate-600">User</th>
                                <th className="px-6 py-4 font-semibold text-slate-600">Role</th>
                                <th className="px-6 py-4 font-semibold text-slate-600">Status</th>
                                <th className="px-6 py-4 font-semibold text-slate-600">Joined</th>
                                <th className="px-6 py-4 font-semibold text-slate-600 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {users.data.map((user) => (
                                <tr key={user.id} className="hover:bg-slate-50/50 transition-colors">
                                    <td className="px-6 py-4">
                                        <div className="flex items-center gap-3">
                                            <div className="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0 border overflow-hidden">
                                                {user.avatar ? (
                                                    <img src={`/storage/${user.avatar}`} alt={user.name} className="h-full w-full object-cover" />
                                                ) : (
                                                    <UserIcon className="h-5 w-5 text-slate-400" />
                                                )}
                                            </div>
                                            <div>
                                                <div className="font-medium text-slate-900">{user.name}</div>
                                                <div className="text-slate-500 text-xs">{user.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="flex flex-wrap gap-1">
                                            {user.roles.length > 0 ? (
                                                user.roles.map(role => (
                                                    <Badge key={role.id} variant="secondary" className="capitalize">
                                                        {role.name}
                                                    </Badge>
                                                ))
                                            ) : (
                                                <span className="text-slate-400 text-xs italic">No role</span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4">
                                        {user.suspended_at ? (
                                            <Badge variant="destructive" className="bg-red-100 text-red-700 hover:bg-red-100 border-red-200">
                                                Suspended
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline" className="bg-emerald-50 text-emerald-700 border-emerald-200">
                                                Active
                                            </Badge>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 text-slate-500">
                                        {new Date(user.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" className="h-8 w-8 p-0">
                                                    <span className="sr-only">Open menu</span>
                                                    <MoreHorizontal className="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                <DropdownMenuItem asChild>
                                                    <Link href={`/admin/users/${user.id}`}>View Profile</Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem 
                                                    onClick={() => toggleSuspension(user)}
                                                    className={user.suspended_at ? 'text-emerald-600' : 'text-red-600'}
                                                >
                                                    {user.suspended_at ? 'Unsuspend User' : 'Suspend User'}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            ))}
                            {users.data.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-6 py-12 text-center text-slate-500">
                                        <div className="flex flex-col items-center justify-center">
                                            <UserIcon className="h-10 w-10 text-slate-300 mb-3" />
                                            <p>No users found matching your filters.</p>
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
                        Showing <span className="font-medium text-slate-900">{users.from || 0}</span> to <span className="font-medium text-slate-900">{users.to || 0}</span> of <span className="font-medium text-slate-900">{users.total}</span> users
                    </div>
                    <div className="flex gap-1">
                        {users.links.map((link, i) => (
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
