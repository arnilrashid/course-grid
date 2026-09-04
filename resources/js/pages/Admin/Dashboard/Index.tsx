import AdminLayout from '@/pages/Admin/Layout';
import { Head, Link } from '@inertiajs/react';
import { 
    DollarSign, ShoppingCart, Users, BookOpen, GraduationCap, Repeat, 
    ArrowUpRight, ArrowDownRight, AlertTriangle, CheckCircle2, Info, Star
} from 'lucide-react';
import { 
    LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer 
} from 'recharts';

interface KpiData {
    current: number;
    period: number;
    trend: number;
}

interface AlertsData {
    pending_courses: number;
    pending_refunds: number;
    failed_payments: number;
    pending_payouts: number;
    failed_jobs: number;
}

interface ActivityItem {
    type: string;
    title: string;
    description: string;
    date: string;
}

interface Props {
    kpis: {
        revenue: KpiData;
        orders: KpiData;
        students: KpiData;
        courses: KpiData;
        instructors: KpiData;
        subscriptions: KpiData;
        alerts: AlertsData;
        activity: ActivityItem[];
    };
}

// Dummy data for the chart since we don't have historical points from the backend yet
const chartData = [
    { name: 'Mon', revenue: 4000, orders: 24 },
    { name: 'Tue', revenue: 3000, orders: 13 },
    { name: 'Wed', revenue: 2000, orders: 98 },
    { name: 'Thu', revenue: 2780, orders: 39 },
    { name: 'Fri', revenue: 1890, orders: 48 },
    { name: 'Sat', revenue: 2390, orders: 38 },
    { name: 'Sun', revenue: 3490, orders: 43 },
];

export default function DashboardIndex({ kpis }: Props) {
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    });

    const formatNumber = (num: number) => {
        return new Intl.NumberFormat('en-US').format(num);
    };

    const renderTrend = (trend: number) => {
        if (trend === 0) return <span className="text-gray-500 text-sm flex items-center">0.0%</span>;
        if (trend > 0) return <span className="text-emerald-600 text-sm flex items-center"><ArrowUpRight className="h-4 w-4 mr-1" />{trend.toFixed(1)}%</span>;
        return <span className="text-rose-600 text-sm flex items-center"><ArrowDownRight className="h-4 w-4 mr-1" />{Math.abs(trend).toFixed(1)}%</span>;
    };

    return (
        <AdminLayout title="Dashboard Overview">
            <Head title="Admin Dashboard" />

            {/* KPI Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
                {/* Revenue */}
                <div className="bg-white rounded-xl shadow-sm border p-5">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-sm font-medium text-slate-500">Total Revenue</h3>
                        <div className="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                            <DollarSign className="h-5 w-5" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-900 mb-1">
                        {formatter.format(kpis.revenue.current)}
                    </p>
                    <div className="flex items-center justify-between">
                        {renderTrend(kpis.revenue.trend)}
                        <span className="text-xs text-slate-400">vs last 30d</span>
                    </div>
                </div>

                {/* Orders */}
                <div className="bg-white rounded-xl shadow-sm border p-5">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-sm font-medium text-slate-500">Total Orders</h3>
                        <div className="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <ShoppingCart className="h-5 w-5" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-900 mb-1">
                        {formatNumber(kpis.orders.current)}
                    </p>
                    <div className="flex items-center justify-between">
                        {renderTrend(kpis.orders.trend)}
                        <span className="text-xs text-slate-400">vs last 30d</span>
                    </div>
                </div>

                {/* Students */}
                <div className="bg-white rounded-xl shadow-sm border p-5">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-sm font-medium text-slate-500">Students</h3>
                        <div className="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <Users className="h-5 w-5" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-900 mb-1">
                        {formatNumber(kpis.students.current)}
                    </p>
                    <div className="flex items-center justify-between">
                        {renderTrend(kpis.students.trend)}
                        <span className="text-xs text-slate-400">vs last 30d</span>
                    </div>
                </div>

                {/* Courses */}
                <div className="bg-white rounded-xl shadow-sm border p-5">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-sm font-medium text-slate-500">Active Courses</h3>
                        <div className="p-2 bg-orange-50 text-orange-600 rounded-lg">
                            <BookOpen className="h-5 w-5" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-900 mb-1">
                        {formatNumber(kpis.courses.current)}
                    </p>
                    <div className="flex items-center justify-between">
                        {renderTrend(kpis.courses.trend)}
                        <span className="text-xs text-slate-400">vs last 30d</span>
                    </div>
                </div>

                {/* Instructors */}
                <div className="bg-white rounded-xl shadow-sm border p-5">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-sm font-medium text-slate-500">Instructors</h3>
                        <div className="p-2 bg-purple-50 text-purple-600 rounded-lg">
                            <GraduationCap className="h-5 w-5" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-900 mb-1">
                        {formatNumber(kpis.instructors.current)}
                    </p>
                    <div className="flex items-center justify-between">
                        {renderTrend(kpis.instructors.trend)}
                        <span className="text-xs text-slate-400">vs last 30d</span>
                    </div>
                </div>

                {/* Subscriptions */}
                <div className="bg-white rounded-xl shadow-sm border p-5">
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-sm font-medium text-slate-500">Subscriptions</h3>
                        <div className="p-2 bg-rose-50 text-rose-600 rounded-lg">
                            <Repeat className="h-5 w-5" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold text-slate-900 mb-1">
                        {formatNumber(kpis.subscriptions.current)}
                    </p>
                    <div className="flex items-center justify-between">
                        {renderTrend(kpis.subscriptions.trend)}
                        <span className="text-xs text-slate-400">vs last 30d</span>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {/* Main Analytics Chart */}
                <div className="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6">
                    <div className="flex items-center justify-between mb-6">
                        <h2 className="text-lg font-bold text-slate-800">Revenue & Orders</h2>
                        <select className="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    <div className="h-80">
                        <ResponsiveContainer width="100%" height="100%">
                            <LineChart data={chartData} margin={{ top: 5, right: 20, bottom: 5, left: 0 }}>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e2e8f0" />
                                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: '#64748b'}} dy={10} />
                                <YAxis yAxisId="left" axisLine={false} tickLine={false} tick={{fill: '#64748b'}} dx={-10} tickFormatter={(value) => `$${value}`} />
                                <YAxis yAxisId="right" orientation="right" axisLine={false} tickLine={false} tick={{fill: '#64748b'}} dx={10} />
                                <Tooltip 
                                    contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }}
                                />
                                <Line yAxisId="left" type="monotone" dataKey="revenue" stroke="#4f46e5" strokeWidth={3} dot={false} activeDot={{ r: 6 }} />
                                <Line yAxisId="right" type="monotone" dataKey="orders" stroke="#0ea5e9" strokeWidth={3} dot={false} activeDot={{ r: 6 }} />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Right Column: Alerts & Activity */}
                <div className="space-y-8">
                    
                    {/* System Alerts */}
                    <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                        <div className="bg-slate-50 border-b px-5 py-4 flex items-center justify-between">
                            <h2 className="text-base font-bold text-slate-800 flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5 text-amber-500" />
                                System Alerts
                            </h2>
                        </div>
                        <div className="divide-y">
                            {kpis.alerts.pending_courses > 0 && (
                                <Link href="/admin/courses" className="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                    <div className="flex items-center gap-3">
                                        <div className="h-2 w-2 rounded-full bg-blue-500"></div>
                                        <span className="text-sm font-medium text-slate-700 group-hover:text-indigo-600 transition-colors">Pending Course Approvals</span>
                                    </div>
                                    <span className="inline-flex items-center justify-center h-6 px-2.5 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                        {kpis.alerts.pending_courses}
                                    </span>
                                </Link>
                            )}
                            
                            {kpis.alerts.pending_refunds > 0 && (
                                <Link href="/admin/refunds" className="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                    <div className="flex items-center gap-3">
                                        <div className="h-2 w-2 rounded-full bg-amber-500"></div>
                                        <span className="text-sm font-medium text-slate-700 group-hover:text-indigo-600 transition-colors">Pending Refunds</span>
                                    </div>
                                    <span className="inline-flex items-center justify-center h-6 px-2.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                        {kpis.alerts.pending_refunds}
                                    </span>
                                </Link>
                            )}

                            {kpis.alerts.pending_payouts > 0 && (
                                <Link href="/admin/payouts" className="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                    <div className="flex items-center gap-3">
                                        <div className="h-2 w-2 rounded-full bg-purple-500"></div>
                                        <span className="text-sm font-medium text-slate-700 group-hover:text-indigo-600 transition-colors">Instructor Payouts Due</span>
                                    </div>
                                    <span className="inline-flex items-center justify-center h-6 px-2.5 rounded-full bg-purple-100 text-purple-700 text-xs font-bold">
                                        {kpis.alerts.pending_payouts}
                                    </span>
                                </Link>
                            )}

                            {kpis.alerts.failed_payments > 0 && (
                                <Link href="/admin/payments" className="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                    <div className="flex items-center gap-3">
                                        <div className="h-2 w-2 rounded-full bg-rose-500"></div>
                                        <span className="text-sm font-medium text-slate-700 group-hover:text-indigo-600 transition-colors">Failed Payments (30d)</span>
                                    </div>
                                    <span className="inline-flex items-center justify-center h-6 px-2.5 rounded-full bg-rose-100 text-rose-700 text-xs font-bold">
                                        {kpis.alerts.failed_payments}
                                    </span>
                                </Link>
                            )}

                            {kpis.alerts.failed_jobs > 0 && (
                                <Link href="/admin/health" className="flex items-center justify-between p-4 hover:bg-slate-50 transition-colors group">
                                    <div className="flex items-center gap-3">
                                        <div className="h-2 w-2 rounded-full bg-red-600 animate-pulse"></div>
                                        <span className="text-sm font-medium text-slate-700 group-hover:text-indigo-600 transition-colors">System Errors (Queue)</span>
                                    </div>
                                    <span className="inline-flex items-center justify-center h-6 px-2.5 rounded-full bg-red-100 text-red-700 text-xs font-bold">
                                        {kpis.alerts.failed_jobs}
                                    </span>
                                </Link>
                            )}

                            {Object.values(kpis.alerts).reduce((a, b) => a + b, 0) === 0 && (
                                <div className="p-6 text-center text-slate-500 text-sm">
                                    <CheckCircle2 className="h-8 w-8 mx-auto mb-2 text-emerald-400" />
                                    All systems operational. No alerts.
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Recent Activity Feed */}
                    <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
                        <div className="bg-slate-50 border-b px-5 py-4">
                            <h2 className="text-base font-bold text-slate-800">Recent Activity</h2>
                        </div>
                        <div className="p-5">
                            {kpis.activity.length > 0 ? (
                                <div className="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                                    {kpis.activity.map((item, index) => (
                                        <div key={index} className="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                            <div className="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-slate-100 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                                                {item.type === 'order' && <ShoppingCart className="h-4 w-4" />}
                                                {item.type === 'user' && <Users className="h-4 w-4" />}
                                                {item.type === 'review' && <Star className="h-4 w-4" />}
                                                {item.type !== 'order' && item.type !== 'user' && item.type !== 'review' && <Info className="h-4 w-4" />}
                                            </div>
                                            <div className="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-100 bg-white shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
                                                <div className="flex items-center justify-between mb-1">
                                                    <div className="font-semibold text-slate-800 text-sm">{item.title}</div>
                                                    <time className="text-xs font-medium text-slate-400">
                                                        {new Date(item.date).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}
                                                    </time>
                                                </div>
                                                <div className="text-sm text-slate-500">{item.description}</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-6 text-slate-500 text-sm">
                                    No recent activity found.
                                </div>
                            )}
                        </div>
                    </div>

                </div>
            </div>
        </AdminLayout>
    );
}
