import { Head } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/app-header-layout';
import { Card, CardContent } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { PlayCircle } from 'lucide-react';

export default function StudentDashboard() {
    // Mock data for enrolled courses
    const enrolledCourses = [
        {
            id: 1,
            title: 'Laravel 11 Masterclass: Build Modern SaaS Applications',
            instructor: 'John Doe',
            thumbnail: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
            progress: 45,
        },
        {
            id: 2,
            title: 'Advanced React & TypeScript Patterns 2026',
            instructor: 'Jane Smith',
            thumbnail: 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&q=80',
            progress: 12,
        },
        {
            id: 3,
            title: 'UI/UX Design for Developers',
            instructor: 'Alice Johnson',
            thumbnail: 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80',
            progress: 100,
        },
    ];

    const breadcrumbs = [
        {
            title: 'My Learning',
            href: '/my-learning',
        },
    ];

    return (
        <AppHeaderLayout breadcrumbs={breadcrumbs}>
            <Head title="My Learning" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-end mb-8">
                        <div>
                            <h1 className="text-4xl font-bold tracking-tight text-slate-900">My Learning</h1>
                            <p className="text-lg text-slate-500 mt-2">Pick up where you left off and complete your goals.</p>
                        </div>
                        <div className="flex space-x-6 border-b border-slate-200">
                            <button className="pb-4 border-b-2 border-slate-900 font-semibold text-slate-900">All courses</button>
                            <button className="pb-4 font-medium text-slate-500 hover:text-slate-700">In progress</button>
                            <button className="pb-4 font-medium text-slate-500 hover:text-slate-700">Completed</button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {enrolledCourses.map((course) => (
                            <Card key={course.id} className="overflow-hidden group hover:shadow-lg transition-all duration-300 border-slate-200">
                                <div className="relative aspect-video overflow-hidden">
                                    <div className="absolute inset-0 bg-slate-900/10 group-hover:bg-slate-900/20 transition-colors z-10" />
                                    <img 
                                        src={course.thumbnail} 
                                        alt={course.title} 
                                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    />
                                    <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20">
                                        <div className="bg-white/90 backdrop-blur-sm p-3 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all">
                                            <PlayCircle className="w-8 h-8 text-slate-900 fill-slate-900" />
                                        </div>
                                    </div>
                                </div>
                                <CardContent className="p-5">
                                    <h3 className="font-semibold text-lg line-clamp-2 leading-tight group-hover:text-indigo-600 transition-colors">
                                        {course.title}
                                    </h3>
                                    <p className="text-sm text-slate-500 mt-2">{course.instructor}</p>
                                    
                                    <div className="mt-5 space-y-2">
                                        <div className="flex justify-between text-xs font-medium">
                                            <span className={course.progress === 100 ? 'text-emerald-600' : 'text-indigo-600'}>
                                                {course.progress === 100 ? 'Completed' : `${course.progress}% Complete`}
                                            </span>
                                        </div>
                                        <Progress value={course.progress} className="h-2" />
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </div>
        </AppHeaderLayout>
    );
}
