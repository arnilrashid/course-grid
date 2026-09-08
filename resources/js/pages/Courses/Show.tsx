import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { PlayCircle, FileText, HelpCircle, FileDown, X } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { login, dashboard } from '@/routes';

// Mock types based on backend structure
type Lesson = {
    id: number;
    title: string;
    type: 'video' | 'text' | 'quiz' | 'resource';
    position: number;
    is_free_preview: boolean;
};

type Section = {
    id: number;
    title: string;
    position: number;
    lessons: Lesson[];
};

type Course = {
    id: number;
    title: string;
    slug: string;
    description: string;
    price: string | number;
    instructor?: { name: string };
    category?: { name: string };
    sections: Section[];
};

export default function CourseShow({ course }: { course: Course }) {
    const { auth } = usePage().props as any;
    const [playingVideoUrl, setPlayingVideoUrl] = useState<string | null>(null);

    const getIconForType = (type: string) => {
        switch (type) {
            case 'video': return <PlayCircle className="w-5 h-5 text-indigo-500" />;
            case 'text': return <FileText className="w-5 h-5 text-slate-500" />;
            case 'quiz': return <HelpCircle className="w-5 h-5 text-amber-500" />;
            case 'resource': return <FileDown className="w-5 h-5 text-emerald-500" />;
            default: return <PlayCircle className="w-5 h-5" />;
        }
    };

    const handleLessonClick = (lesson: Lesson) => {
        if (lesson.type === 'video') {
            if (lesson.is_free_preview || (auth && auth.user)) {
                setPlayingVideoUrl(`/courses/${course.slug}/lessons/${lesson.id}/video`);
            } else {
                alert('Please enroll to view this video.');
            }
        }
    };

    return (
        <div className="min-h-screen bg-slate-50">
            <Head title={course.title} />

            {/* Header */}
            <header className="bg-white border-b border-slate-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                    <Link href="/" className="font-bold text-xl text-slate-900">CourseGrid</Link>
                    <nav>
                        {auth?.user ? (
                            <Link href={dashboard()} className="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                Dashboard
                            </Link>
                        ) : (
                            <Link href={login()} className="text-sm font-medium text-slate-600 hover:text-slate-900">
                                Log in
                            </Link>
                        )}
                    </nav>
                </div>
            </header>

            {/* Hero Section */}
            <div className="bg-slate-900 text-white py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="max-w-3xl">
                        <h1 className="text-4xl font-bold mb-4">{course.title}</h1>
                        <p className="text-xl text-slate-300 mb-6">{course.description}</p>
                        <div className="flex items-center space-x-4 text-sm text-slate-400">
                            {course.instructor && <span>Created by {course.instructor.name}</span>}
                            {course.category && <span>• {course.category.name}</span>}
                        </div>
                    </div>
                </div>
            </div>

            {/* Curriculum */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col lg:flex-row gap-8">
                <div className="flex-1">
                    <h2 className="text-2xl font-bold text-slate-900 mb-6">Course Curriculum</h2>
                    
                    <div className="space-y-4">
                        {course.sections?.map((section) => (
                            <Card key={section.id} className="border border-slate-200">
                                <div className="bg-slate-50 px-6 py-4 font-semibold text-slate-800 border-b border-slate-200">
                                    Section {section.position}: {section.title}
                                </div>
                                <div className="divide-y divide-slate-100">
                                    {section.lessons?.map((lesson) => (
                                        <div 
                                            key={lesson.id} 
                                            onClick={() => handleLessonClick(lesson)}
                                            className={`px-6 py-4 flex justify-between items-center group transition-colors ${lesson.type === 'video' && (lesson.is_free_preview || auth?.user) ? 'cursor-pointer hover:bg-slate-50' : 'cursor-default'}`}
                                        >
                                            <div className="flex items-center space-x-3">
                                                {getIconForType(lesson.type)}
                                                <span className={`text-sm ${lesson.type === 'video' && (lesson.is_free_preview || auth?.user) ? 'group-hover:text-indigo-600' : 'text-slate-700'}`}>
                                                    {lesson.position}. {lesson.title}
                                                </span>
                                            </div>
                                            <div>
                                                {lesson.is_free_preview && lesson.type === 'video' && (
                                                    <span className="text-xs font-semibold bg-indigo-100 text-indigo-700 px-2 py-1 rounded">
                                                        Preview
                                                    </span>
                                                )}
                                                {!lesson.is_free_preview && lesson.type === 'video' && !auth?.user && (
                                                    <span className="text-xs font-semibold bg-slate-100 text-slate-500 px-2 py-1 rounded">
                                                        Locked
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                    {(!section.lessons || section.lessons.length === 0) && (
                                        <div className="px-6 py-4 text-sm text-slate-500 italic">No lessons in this section.</div>
                                    )}
                                </div>
                            </Card>
                        ))}
                        {(!course.sections || course.sections.length === 0) && (
                            <p className="text-slate-500">Curriculum not available yet.</p>
                        )}
                    </div>
                </div>
                
                {/* Sidebar */}
                <div className="w-full lg:w-96">
                    <Card className="sticky top-6 border border-slate-200 shadow-xl">
                        <CardContent className="p-6">
                            <div className="text-3xl font-bold text-slate-900 mb-6">
                                ${course.price}
                            </div>
                            <button className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded transition-colors mb-4">
                                Enroll Now
                            </button>
                            <p className="text-xs text-center text-slate-500">
                                30-Day Money-Back Guarantee
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {/* Video Modal */}
            {playingVideoUrl && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm p-4">
                    <div className="relative w-full max-w-5xl aspect-video bg-black rounded-lg overflow-hidden shadow-2xl">
                        <button 
                            onClick={() => setPlayingVideoUrl(null)}
                            className="absolute top-4 right-4 z-10 text-white hover:text-red-500 transition-colors bg-black/50 rounded-full p-1"
                        >
                            <X className="w-6 h-6" />
                        </button>
                        <video 
                            src={playingVideoUrl} 
                            controls 
                            autoPlay 
                            className="w-full h-full"
                            controlsList="nodownload"
                        >
                            Your browser does not support HTML video.
                        </video>
                    </div>
                </div>
            )}
        </div>
    );
}
