import { Head, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { PlusCircle, MoreHorizontal, Users, Star, TrendingUp } from 'lucide-react';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export default function InstructorDashboard() {
    // Mock data for instructor courses
    const courses = [
        {
            id: 1,
            title: 'Laravel 11 Masterclass: Build Modern SaaS Applications',
            status: 'Published',
            students: 1245,
            rating: 4.8,
            revenue: '$12,450',
            updated: '2 days ago'
        },
        {
            id: 2,
            title: 'Advanced React & TypeScript Patterns 2026',
            status: 'Draft',
            students: 0,
            rating: 0,
            revenue: '$0',
            updated: '5 hours ago'
        },
    ];

    const breadcrumbs = [
        {
            title: 'Instructor Dashboard',
            href: '/instructor/dashboard',
        },
        {
            title: 'Courses',
            href: '/instructor/courses',
        }
    ];

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title="Instructor Dashboard" />

            <div className="flex h-full w-full flex-1 flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Courses</h1>
                        <p className="text-slate-500 mt-1">Manage your courses, view performance, and communicate with students.</p>
                    </div>
                    <Button className="gap-2">
                        <PlusCircle className="h-4 w-4" />
                        Create Course
                    </Button>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
                            <TrendingUp className="h-4 w-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">$12,450</div>
                            <p className="text-xs text-slate-500">+20% from last month</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <Users className="h-4 w-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">1,245</div>
                            <p className="text-xs text-slate-500">+180 new this month</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Instructor Rating</CardTitle>
                            <Star className="h-4 w-4 text-slate-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">4.8</div>
                            <p className="text-xs text-slate-500">Based on 342 reviews</p>
                        </CardContent>
                    </Card>
                </div>

                <Card className="mt-4 border-slate-200">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left">
                            <thead className="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th className="px-6 py-4 font-medium">Course Title</th>
                                    <th className="px-6 py-4 font-medium">Status</th>
                                    <th className="px-6 py-4 font-medium text-right">Students</th>
                                    <th className="px-6 py-4 font-medium text-right">Revenue</th>
                                    <th className="px-6 py-4 font-medium">Last Updated</th>
                                    <th className="px-6 py-4 font-medium"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {courses.map((course) => (
                                    <tr key={course.id} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td className="px-6 py-4 font-medium text-slate-900">
                                            {course.title}
                                        </td>
                                        <td className="px-6 py-4">
                                            <Badge variant={course.status === 'Published' ? 'default' : 'secondary'} className={course.status === 'Published' ? 'bg-emerald-500 hover:bg-emerald-600' : ''}>
                                                {course.status}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4 text-right tabular-nums text-slate-600">
                                            {course.students.toLocaleString()}
                                        </td>
                                        <td className="px-6 py-4 text-right tabular-nums text-slate-600">
                                            {course.revenue}
                                        </td>
                                        <td className="px-6 py-4 text-slate-500">
                                            {course.updated}
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
                                                    <DropdownMenuItem>Edit Course</DropdownMenuItem>
                                                    <DropdownMenuItem>View Analytics</DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem className="text-red-600">Delete</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </AppSidebarLayout>
    );
}
