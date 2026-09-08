import AdminLayout from '@/pages/Admin/Layout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import { 
    User as UserIcon, Mail, Calendar, Shield, Activity, MonitorSmartphone, 
    AlertTriangle, ChevronLeft, PowerOff, Camera, XCircle
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from '@/components/ui/label';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    avatar: string | null;
    suspended_at: string | null;
    created_at: string;
    roles: Role[];
}

interface Session {
    id: string;
    ip_address: string;
    user_agent: string;
    last_activity: string;
}

interface AuditLog {
    id: number;
    action: string;
    created_at: string;
    old_values: any;
    new_values: any;
    user?: User;
}

interface Props {
    user: User;
    activity: { data: AuditLog[] };
    sessions: Session[];
    availableRoles: Role[];
    coursesCount: number;
    has_2fa: boolean;
}

export default function UserShow({ user, activity, sessions, availableRoles, coursesCount, has_2fa }: Props) {
    const { url } = usePage();
    const searchString = typeof window !== 'undefined' ? window.location.search : '';
    const urlParams = new URLSearchParams(searchString);
    const initialTab = urlParams.get('tab') || 'profile';
    const [activeTab, setActiveTab] = useState(initialTab);
    const [selectedRole, setSelectedRole] = useState<string>(user.roles.length > 0 ? user.roles[0].name : 'none');
    
    // Delete User state
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const { data: deleteData, setData: setDeleteData, delete: destroyUser, processing: isDeleting } = useForm({
        transfer_to_user_id: '',
    });

    // Update URL when tab changes so refresh keeps the same tab
    const handleTabChange = (tab: string) => {
        setActiveTab(tab);
        router.get(`/admin/users/${user.id}?tab=${tab}`, {}, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    };

    // Poll for real-time sessions or activity data when in those tabs
    useEffect(() => {
        let interval: NodeJS.Timeout;
        if (activeTab === 'security' || activeTab === 'activity') {
            interval = setInterval(() => {
                router.reload({ 
                    only: activeTab === 'security' ? ['sessions'] : ['activity'], 
                    showProgress: false,
                });
            }, 3000); // Refresh every 3 seconds for near real-time updates
        }
        return () => {
            if (interval) clearInterval(interval);
        };
    }, [activeTab]);

    // Profile Edit Form
    const { data: profileData, setData: setProfileData, post: postProfile, processing: profileProcessing, errors: profileErrors } = useForm({
        _method: 'PUT',
        name: user.name,
        email: user.email,
        password: '',
        avatar: null as File | null,
    });

    const handleRoleUpdate = () => {
        router.post(`/admin/users/${user.id}/role`, {
            role: selectedRole === 'none' ? null : selectedRole
        }, {
            preserveScroll: true,
        });
    };

    const handleRevokeSessions = () => {
        router.post(`/admin/users/${user.id}/revoke-sessions`, {}, {
            preserveScroll: true,
        });
    };

    const handleRevokeSession = (sessionId: string) => {
        router.delete(`/admin/users/${user.id}/sessions/${sessionId}`, {
            preserveScroll: true,
        });
    };

    const toggleSuspension = () => {
        const action = user.suspended_at ? 'unsuspend' : 'suspend';
        
        router.post(`/admin/users/${user.id}/${action}`, { reason: '' }, {
            preserveScroll: true,
        });
    };

    const submitProfile = (e: React.FormEvent) => {
        e.preventDefault();
        // Inertia doesn't support PUT with FormData out of the box, we spoof it with POST + _method=PUT
        postProfile(`/admin/users/${user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setProfileData('password', '');
                setProfileData('avatar', null);
                const fileInput = document.getElementById('avatar-upload') as HTMLInputElement;
                if (fileInput) fileInput.value = '';
            }
        });
    };

    const handleDeleteUser = (e: React.FormEvent) => {
        e.preventDefault();
        destroyUser(`/admin/users/${user.id}`, {
            onSuccess: () => setIsDeleteDialogOpen(false)
        });
    };

    return (
        <AdminLayout title="User Profile">
            <Head title={`${user.name} - Users`} />

            <div className="mb-6">
                <Link href="/admin/users" className="text-sm font-medium text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <ChevronLeft className="h-4 w-4" />
                    Back to Users
                </Link>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* Left Sidebar Profile Card */}
                <div className="lg:col-span-1 space-y-6">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex flex-col items-center text-center">
                                <div className="h-24 w-24 rounded-full bg-slate-100 flex items-center justify-center border-4 border-white shadow-sm mb-4 overflow-hidden relative group">
                                    {user.avatar ? (
                                        <img src={user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`} alt={user.name} className="h-full w-full object-cover" referrerPolicy="no-referrer" />
                                    ) : (
                                        <UserIcon className="h-12 w-12 text-slate-400" />
                                    )}
                                </div>
                                <h2 className="text-xl font-bold text-slate-900">{user.name}</h2>
                                <div className="flex items-center gap-1 text-slate-500 text-sm mt-1 mb-4">
                                    <Mail className="h-4 w-4" />
                                    {user.email}
                                </div>
                                <div className="flex flex-wrap justify-center gap-2 mb-6">
                                    {user.suspended_at ? (
                                        <Badge variant="destructive">Suspended</Badge>
                                    ) : (
                                        <Badge variant="outline" className="bg-emerald-50 text-emerald-700 border-emerald-200">Active</Badge>
                                    )}
                                    {user.roles.map(role => (
                                        <Badge key={role.id} variant="secondary" className="capitalize">{role.name}</Badge>
                                    ))}
                                </div>
                                
                                <div className="w-full pt-4 border-t border-slate-100 space-y-2">
                                    {(!user.roles.some(r => r.name === 'admin') && user.id !== (usePage().props.auth as any).user.id) && (
                                        <Button 
                                            variant="outline" 
                                            className="w-full"
                                            onClick={() => router.post(`/admin/users/${user.id}/impersonate`)}
                                        >
                                            Login as {user.name}
                                        </Button>
                                    )}

                                    {!user.roles.some(r => r.name === 'admin') && (
                                        <Button 
                                            variant={user.suspended_at ? "outline" : "secondary"} 
                                            className="w-full"
                                            onClick={toggleSuspension}
                                        >
                                            {user.suspended_at ? 'Unsuspend User' : 'Suspend User'}
                                        </Button>
                                    )}

                                    {!user.roles.some(r => r.name === 'admin') && (
                                        <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                                            <DialogTrigger asChild>
                                                <Button variant="destructive" className="w-full">
                                                    Delete User
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <form onSubmit={handleDeleteUser}>
                                                    <DialogHeader>
                                                        <DialogTitle>Delete User</DialogTitle>
                                                        <DialogDescription>
                                                            Are you sure you want to delete {user.name}? This will anonymize their personal details and permanently revoke their access.
                                                        </DialogDescription>
                                                    </DialogHeader>
                                                    
                                                    {coursesCount > 0 && (
                                                        <div className="my-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                                            <div className="flex items-start gap-3">
                                                                <AlertTriangle className="h-5 w-5 text-amber-600 mt-0.5" />
                                                                <div>
                                                                    <h4 className="text-sm font-semibold text-amber-800">Instructor Warning</h4>
                                                                    <p className="text-sm text-amber-700 mt-1">
                                                                        This user has <strong>{coursesCount} published courses</strong>. 
                                                                        If you do not transfer these courses, they will automatically be transferred to the Super Admin (ID: 1).
                                                                    </p>
                                                                    
                                                                    <div className="mt-4">
                                                                        <Label htmlFor="transfer_to_user_id" className="text-amber-800">Transfer Courses to User ID (Optional)</Label>
                                                                        <Input 
                                                                            id="transfer_to_user_id" 
                                                                            type="number" 
                                                                            placeholder="e.g. 2" 
                                                                            className="mt-1.5 border-amber-300 focus-visible:ring-amber-500"
                                                                            value={deleteData.transfer_to_user_id}
                                                                            onChange={e => setDeleteData('transfer_to_user_id', e.target.value)}
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    )}

                                                    <DialogFooter className="mt-6">
                                                        <Button type="button" variant="outline" onClick={() => setIsDeleteDialogOpen(false)}>Cancel</Button>
                                                        <Button type="submit" variant="destructive" disabled={isDeleting}>
                                                            {isDeleting ? 'Deleting...' : 'Confirm Deletion'}
                                                        </Button>
                                                    </DialogFooter>
                                                </form>
                                            </DialogContent>
                                        </Dialog>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="text-sm">Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm">
                            <div className="flex items-center justify-between">
                                <span className="text-slate-500 flex items-center gap-2"><Calendar className="h-4 w-4"/> Joined</span>
                                <span className="font-medium">{new Date(user.created_at).toLocaleDateString()}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-slate-500 flex items-center gap-2"><MonitorSmartphone className="h-4 w-4"/> Sessions</span>
                                <span className="font-medium">{sessions.length} Active</span>
                            </div>
                            <div className="flex flex-col gap-2 pt-4 border-t border-slate-100">
                                <span className="text-slate-500 flex items-center gap-2 font-medium mb-1"><Shield className="h-4 w-4"/> Security</span>
                                
                                {!user.roles.some(r => r.name === 'admin') && (
                                    <>
                                        {!user.email_verified_at ? (
                                            <div className="flex items-center justify-between gap-2">
                                                <span className="text-amber-600 flex items-center gap-1"><AlertTriangle className="h-3 w-3"/> Unverified Email</span>
                                                <div className="flex gap-1">
                                                    <Button size="sm" variant="outline" onClick={() => router.post(`/admin/users/${user.id}/resend-verification`)}>Resend Link</Button>
                                                    <Button size="sm" onClick={() => router.post(`/admin/users/${user.id}/verify-email`)}>Verify Now</Button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="flex items-center justify-between">
                                                <span className="text-slate-600">Email Status</span>
                                                <Badge variant="outline" className="bg-emerald-50 text-emerald-700 border-emerald-200">Verified</Badge>
                                            </div>
                                        )}

                                        {has_2fa && (
                                            <div className="flex items-center justify-between gap-2 mt-2">
                                                <span className="text-slate-600">Two-Factor Auth</span>
                                                <Button size="sm" variant="destructive" onClick={() => {
                                                    if (confirm('Are you sure you want to disable 2FA for this user? They will need to set it up again.')) {
                                                        router.post(`/admin/users/${user.id}/disable-2fa`);
                                                    }
                                                }}>Disable 2FA</Button>
                                            </div>
                                        )}
                                    </>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Right Content Area */}
                <div className="lg:col-span-3">
                    {/* Custom Tabs */}
                    <div className="bg-white rounded-xl shadow-sm border mb-6 flex overflow-x-auto">
                        <button 
                            onClick={() => handleTabChange('profile')}
                            className={`px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap flex items-center gap-2 ${activeTab === 'profile' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'}`}
                        >
                            <UserIcon className="h-4 w-4" /> Edit Profile
                        </button>
                        <button 
                            onClick={() => handleTabChange('roles')}
                            className={`px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap flex items-center gap-2 ${activeTab === 'roles' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'}`}
                        >
                            <Shield className="h-4 w-4" /> Role & Permissions
                        </button>
                        <button 
                            onClick={() => handleTabChange('security')}
                            className={`px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap flex items-center gap-2 ${activeTab === 'security' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'}`}
                        >
                            <MonitorSmartphone className="h-4 w-4" /> Sessions & Security
                        </button>
                        <button 
                            onClick={() => handleTabChange('activity')}
                            className={`px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap flex items-center gap-2 ${activeTab === 'activity' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'}`}
                        >
                            <Activity className="h-4 w-4" /> Audit Log
                        </button>
                    </div>

                    {/* Tab Contents */}
                    <div className="space-y-6">
                        {activeTab === 'profile' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Edit Profile</CardTitle>
                                    <CardDescription>Manage user's general information and password.</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form onSubmit={submitProfile} className="space-y-6 max-w-xl">
                                        <div className="space-y-2">
                                            <label className="text-sm font-medium text-slate-700">Profile Picture</label>
                                            <div className="flex items-center gap-4">
                                                <div className="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center border shrink-0 overflow-hidden">
                                                    {profileData.avatar ? (
                                                        <img src={URL.createObjectURL(profileData.avatar)} alt="Preview" className="h-full w-full object-cover" />
                                                    ) : user.avatar ? (
                                                        <img src={user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`} alt={user.name} className="h-full w-full object-cover" referrerPolicy="no-referrer" />
                                                    ) : (
                                                        <Camera className="h-6 w-6 text-slate-400" />
                                                    )}
                                                </div>
                                                <div className="flex-1">
                                                    <Input 
                                                        id="avatar-upload"
                                                        type="file" 
                                                        accept="image/*"
                                                        onChange={(e) => setProfileData('avatar', e.target.files?.[0] || null)}
                                                        className="text-sm"
                                                    />
                                                    <p className="text-xs text-slate-500 mt-1">Recommended size 2000x2000px. Max 20MB.</p>
                                                    {profileErrors.avatar && <p className="text-sm text-red-500 mt-1">{profileErrors.avatar}</p>}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <label className="text-sm font-medium text-slate-700">Full Name</label>
                                            <Input 
                                                type="text" 
                                                value={profileData.name} 
                                                onChange={e => setProfileData('name', e.target.value)} 
                                            />
                                            {profileErrors.name && <p className="text-sm text-red-500">{profileErrors.name}</p>}
                                        </div>

                                        <div className="space-y-2">
                                            <label className="text-sm font-medium text-slate-700">Email Address</label>
                                            <Input 
                                                type="email" 
                                                value={profileData.email} 
                                                onChange={e => setProfileData('email', e.target.value)} 
                                            />
                                            {profileErrors.email && <p className="text-sm text-red-500">{profileErrors.email}</p>}
                                        </div>

                                        <div className="space-y-2">
                                            <label className="text-sm font-medium text-slate-700">New Password <span className="text-slate-400 font-normal">(Leave blank to keep current)</span></label>
                                            <Input 
                                                type="password" 
                                                value={profileData.password} 
                                                onChange={e => setProfileData('password', e.target.value)} 
                                                placeholder="••••••••"
                                            />
                                            {profileErrors.password && <p className="text-sm text-red-500">{profileErrors.password}</p>}
                                        </div>

                                        <Button type="submit" disabled={profileProcessing}>
                                            Save Changes
                                        </Button>
                                    </form>
                                </CardContent>
                            </Card>
                        )}

                        {activeTab === 'roles' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Role Assignment</CardTitle>
                                    <CardDescription>Manage the user's primary system role.</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-end gap-4 max-w-md">
                                        <div className="flex-1 space-y-2">
                                            <label className="text-sm font-medium text-slate-700">Primary Role</label>
                                            <Select value={selectedRole} onValueChange={setSelectedRole}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a role" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="none">No Role</SelectItem>
                                                    {availableRoles.map(role => (
                                                        <SelectItem key={role.id} value={role.name}>
                                                            {role.name.charAt(0).toUpperCase() + role.name.slice(1)}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <Button onClick={handleRoleUpdate} disabled={selectedRole === (user.roles[0]?.name || 'none')}>
                                            Update Role
                                        </Button>
                                    </div>
                                    <div className="mt-6 p-4 bg-blue-50 text-blue-800 rounded-lg flex gap-3 text-sm">
                                        <AlertTriangle className="h-5 w-5 shrink-0" />
                                        <div>
                                            <p className="font-semibold mb-1">Role-based Access</p>
                                            <p>Assigning a role grants all permissions associated with that role. You can edit the permissions attached to roles in the <Link href="/admin/roles" className="underline font-semibold">Roles management section</Link>.</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {activeTab === 'security' && (
                            <Card>
                                <CardHeader className="flex flex-row items-center justify-between">
                                    <div>
                                        <CardTitle>Active Sessions</CardTitle>
                                        <CardDescription>Devices currently logged into this account.</CardDescription>
                                    </div>
                                    <Button variant="destructive" size="sm" onClick={handleRevokeSessions} disabled={sessions.length === 0}>
                                        <PowerOff className="h-4 w-4 mr-2" /> Revoke All Sessions
                                    </Button>
                                </CardHeader>
                                <CardContent>
                                    {sessions.length > 0 ? (
                                        <div className="divide-y divide-slate-100 border rounded-lg">
                                            {sessions.map((session, i) => (
                                                <div key={i} className="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                                    <div className="flex items-center gap-4">
                                                        <div className="h-10 w-10 bg-white border rounded-lg flex items-center justify-center shrink-0">
                                                            <MonitorSmartphone className="h-5 w-5 text-slate-400" />
                                                        </div>
                                                        <div>
                                                            <p className="font-medium text-sm text-slate-900">{session.ip_address}</p>
                                                            <p className="text-xs text-slate-500 mt-1 max-w-md truncate" title={session.user_agent}>
                                                                {session.user_agent}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center gap-4">
                                                        <div className="text-xs text-slate-500 whitespace-nowrap text-right">
                                                            Last active<br/>
                                                            <span className="font-medium">{session.last_activity}</span>
                                                        </div>
                                                        <Button 
                                                            variant="outline" 
                                                            size="sm" 
                                                            className="text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200"
                                                            onClick={() => handleRevokeSession(session.id)}
                                                        >
                                                            <XCircle className="h-4 w-4 mr-1" />
                                                            Revoke
                                                        </Button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-8 text-slate-500 border rounded-lg bg-slate-50/50">
                                            <MonitorSmartphone className="h-8 w-8 text-slate-300 mx-auto mb-3" />
                                            <p>No active sessions found.</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {activeTab === 'activity' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Audit Log</CardTitle>
                                    <CardDescription>Recent administrative actions related to this user.</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {activity.data.length > 0 ? (
                                        <div className="space-y-4">
                                            {activity.data.map(log => (
                                                <div key={log.id} className="flex gap-4 p-4 rounded-lg border border-slate-100 bg-slate-50/50">
                                                    <div className="mt-1 bg-white p-2 border rounded-full shrink-0">
                                                        <Activity className="h-4 w-4 text-indigo-500" />
                                                    </div>
                                                    <div className="flex-1">
                                                        <div className="flex items-center justify-between mb-1">
                                                            <div className="flex flex-col">
                                                                <p className="font-semibold text-sm capitalize text-slate-800">{log.action.replace('_', ' ')}</p>
                                                                <span className="text-xs text-slate-500">
                                                                    By: {log.user ? log.user.name : 'Unknown/System'}
                                                                </span>
                                                            </div>
                                                            <span className="text-xs text-slate-500 self-start">
                                                                {new Date(log.created_at).toLocaleString()}
                                                            </span>
                                                        </div>
                                                        <div className="text-xs text-slate-600 bg-white p-3 rounded border">
                                                            <div className="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <p className="font-semibold text-slate-400 mb-1">From</p>
                                                                    <pre className="whitespace-pre-wrap font-mono text-[10px]">{JSON.stringify(log.old_values, null, 2)}</pre>
                                                                </div>
                                                                <div>
                                                                    <p className="font-semibold text-slate-400 mb-1">To</p>
                                                                    <pre className="whitespace-pre-wrap font-mono text-[10px]">{JSON.stringify(log.new_values, null, 2)}</pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-8 text-slate-500 border rounded-lg bg-slate-50/50">
                                            <Activity className="h-8 w-8 text-slate-300 mx-auto mb-3" />
                                            <p>No audit logs found for this user.</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
