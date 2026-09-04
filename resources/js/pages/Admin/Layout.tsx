import { Link, usePage } from '@inertiajs/react';
import React, { ReactNode } from 'react';
import { 
    LayoutDashboard, 
    BookOpen, Layers, Grid, HelpCircle, Award, 
    Users, GraduationCap, Building, 
    ShoppingCart, CreditCard, Undo, Tag, Repeat,
    DollarSign, Wallet, ArrowUpRight,
    Star, MessageSquare, Megaphone, Bell,
    FileText, Hash,
    BarChart3,
    Shield, ClipboardList, Activity, Webhook,
    Settings
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
    children: ReactNode;
    title?: string;
}

type NavItem = {
    name: string;
    href: string;
    icon?: React.ElementType;
};

type NavGroup = {
    label: string;
    items: NavItem[];
};

export default function AdminLayout({ children, title }: Props) {
    const { url } = usePage();

    const navigation: NavGroup[] = [
        {
            label: '',
            items: [
                { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
            ]
        },
        {
            label: 'LEARNING',
            items: [
                { name: 'Courses', href: '/admin/courses', icon: BookOpen },
                { name: 'Course Bundles', href: '/admin/bundles', icon: Layers },
                { name: 'Categories', href: '/admin/categories', icon: Grid },
                { name: 'Quizzes', href: '/admin/quizzes', icon: HelpCircle },
                { name: 'Certificates', href: '/admin/certificates', icon: Award },
            ]
        },
        {
            label: 'PEOPLE',
            items: [
                { name: 'Students', href: '/admin/students', icon: Users },
                { name: 'Instructors', href: '/admin/instructors', icon: GraduationCap },
                { name: 'Organizations', href: '/admin/organizations', icon: Building },
            ]
        },
        {
            label: 'COMMERCE',
            items: [
                { name: 'Orders', href: '/admin/orders', icon: ShoppingCart },
                { name: 'Payments', href: '/admin/payments', icon: CreditCard },
                { name: 'Refunds', href: '/admin/refunds', icon: Undo },
                { name: 'Coupons', href: '/admin/coupons', icon: Tag },
                { name: 'Subscriptions', href: '/admin/subscriptions', icon: Repeat },
            ]
        },
        {
            label: 'FINANCE',
            items: [
                { name: 'Revenue', href: '/admin/revenue', icon: DollarSign },
                { name: 'Earnings', href: '/admin/earnings', icon: Wallet },
                { name: 'Payouts', href: '/admin/payouts', icon: ArrowUpRight },
            ]
        },
        {
            label: 'ENGAGEMENT',
            items: [
                { name: 'Reviews', href: '/admin/reviews', icon: Star },
                { name: 'Q&A', href: '/admin/qa', icon: HelpCircle },
                { name: 'Announcements', href: '/admin/announcements', icon: Megaphone },
                { name: 'Messages', href: '/admin/messages', icon: MessageSquare },
                { name: 'Notifications', href: '/admin/notifications', icon: Bell },
            ]
        },
        {
            label: 'CONTENT',
            items: [
                { name: 'Pages', href: '/admin/pages', icon: FileText },
                { name: 'Posts', href: '/admin/posts', icon: FileText },
                { name: 'Tags', href: '/admin/tags', icon: Hash },
            ]
        },
        {
            label: 'REPORTS',
            items: [
                { name: 'Sales', href: '/admin/reports/sales', icon: BarChart3 },
                { name: 'Courses', href: '/admin/reports/courses', icon: BarChart3 },
                { name: 'Students', href: '/admin/reports/students', icon: BarChart3 },
                { name: 'Instructors', href: '/admin/reports/instructors', icon: BarChart3 },
            ]
        },
        {
            label: 'SYSTEM',
            items: [
                { name: 'Users', href: '/admin/users', icon: Users },
                { name: 'System Roles', href: '/admin/roles', icon: Shield },
                { name: 'Audit Logs', href: '/admin/audit-logs', icon: ClipboardList },
                { name: 'Webhooks', href: '/admin/webhooks', icon: Webhook },
                { name: 'System Health', href: '/admin/health', icon: Activity },
            ]
        },
        {
            label: 'SETTINGS',
            items: [
                { name: 'Settings', href: '/admin/settings', icon: Settings },
            ]
        }
    ];

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col md:flex-row">
            {/* Sidebar */}
            <aside className="w-full md:w-64 bg-slate-900 text-slate-300 flex-shrink-0 h-screen overflow-y-auto">
                <div className="p-4 flex items-center justify-between sticky top-0 bg-slate-900 z-10">
                    <h1 className="text-xl font-bold text-white tracking-tight">CourseGrid Admin</h1>
                    <Link href="/" className="text-xs text-slate-400 hover:text-white uppercase tracking-wider font-semibold">Exit</Link>
                </div>
                
                <div className="px-3 pb-8 space-y-6">
                    {navigation.map((group, i) => (
                        <div key={i}>
                            {group.label && (
                                <h3 className="px-3 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    {group.label}
                                </h3>
                            )}
                            <nav className="space-y-1">
                                {group.items.map((item) => {
                                    const isActive = url === item.href || (item.href !== '/admin' && url.startsWith(item.href));
                                    const Icon = item.icon;
                                    
                                    return (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className={`flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                                isActive
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'hover:bg-slate-800 hover:text-white'
                                            }`}
                                        >
                                            {Icon && <Icon className={`h-4 w-4 ${isActive ? 'text-indigo-200' : 'text-slate-400'}`} />}
                                            {item.name}
                                        </Link>
                                    );
                                })}
                            </nav>
                        </div>
                    ))}
                </div>
            </aside>

            {/* Main Content */}
            <div className="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
                <header className="bg-white border-b shadow-sm z-10 sticky top-0">
                    <div className="px-6 py-4 flex items-center justify-between">
                        <h2 className="text-xl font-semibold text-slate-800">{title || 'Admin Dashboard'}</h2>
                        <div className="flex items-center gap-4">
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <button className="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-semibold text-sm hover:bg-slate-300 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 overflow-hidden border">
                                        {usePage().props.auth.user.avatar ? (
                                            <img src={`/storage/${usePage().props.auth.user.avatar}`} alt="Avatar" className="h-full w-full object-cover" />
                                        ) : (
                                            (usePage().props.auth.user.name as string).charAt(0).toUpperCase()
                                        )}
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>My Account</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link href={`/admin/users/${usePage().props.auth.user.id}`}>Profile Settings</Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem asChild>
                                        <Link href="/logout" method="post" as="button" className="w-full text-left text-red-600 cursor-pointer">
                                            Log Out
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </header>

                <main className="flex-1 p-6 overflow-auto bg-slate-50/50">
                    {children}
                </main>
            </div>
        </div>
    );
}
