import AdminLayout from '@/pages/Admin/Layout';
import { Head, Link, router } from '@inertiajs/react';
import { PlusCircle, Edit, Eye, Trash2, CheckCircle, XCircle } from 'lucide-react';
import React from 'react';

interface Course {
    id: number;
    title: string;
    slug: string;
    status: string;
    created_at: string;
    instructor?: {
        name: string;
        email: string;
    };
    category?: {
        name: string;
    };
}

interface Props {
    courses: {
        data: Course[];
        links: any[];
    };
    filters?: {
        search?: string;
        status?: string;
    };
}

export default function CoursesIndex({ courses, filters }: Props) {
    const handleApprove = (course: Course) => {
        if (!confirm(`Are you sure you want to approve "${course.title}"?`)) return;
        router.post(`/admin/courses/${course.id}/approve`, {}, { preserveScroll: true });
    };

    const handleReject = (course: Course) => {
        const reason = prompt('Please provide a reason for rejecting this course:');
        if (reason === null) return;
        if (reason.trim() === '') {
            alert('A rejection reason is required.');
            return;
        }
        router.post(`/admin/courses/${course.id}/reject`, { reason }, { preserveScroll: true });
    };

    const handleDelete = (course: Course) => {
        if (!confirm(`Are you sure you want to delete "${course.title}"? This cannot be undone.`)) return;
        router.delete(`/admin/courses/${course.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout title="Courses">
            <Head title="Admin - Courses" />

            <div className="mb-6 flex justify-between items-center">
                <h1 className="text-2xl font-semibold text-gray-900">Courses</h1>
                <Link 
                    href="/admin/courses/create" 
                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    <PlusCircle className="w-4 h-4 mr-2" />
                    Create Course
                </Link>
            </div>

            <div className="bg-white rounded shadow-sm border overflow-x-auto">
                <table className="w-full text-left text-sm whitespace-nowrap">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="px-6 py-3 font-medium text-gray-500">Course</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Instructor</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Status</th>
                            <th className="px-6 py-3 font-medium text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {courses.data.map((course) => (
                            <tr key={course.id}>
                                <td className="px-6 py-4 font-medium text-gray-900">
                                    {course.title}
                                    <div className="text-xs text-gray-500 font-normal mt-1">{course.category?.name || 'Uncategorized'}</div>
                                </td>
                                <td className="px-6 py-4">
                                    {course.instructor?.name}
                                    <div className="text-xs text-gray-500">{course.instructor?.email}</div>
                                </td>
                                <td className="px-6 py-4">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                        course.status === 'published' ? 'bg-green-100 text-green-800' :
                                        course.status === 'in_review' ? 'bg-yellow-100 text-yellow-800' :
                                        course.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                        'bg-gray-100 text-gray-800'
                                    }`}>
                                        {course.status.replace('_', ' ').toUpperCase()}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-right space-x-2">
                                    {course.status === 'in_review' && (
                                        <>
                                            <button onClick={() => handleApprove(course)} className="text-green-600 hover:text-green-800" title="Approve">
                                                <CheckCircle className="w-5 h-5 inline" />
                                            </button>
                                            <button onClick={() => handleReject(course)} className="text-red-600 hover:text-red-800" title="Reject">
                                                <XCircle className="w-5 h-5 inline" />
                                            </button>
                                        </>
                                    )}
                                    <Link href={`/admin/courses/${course.id}`} className="text-blue-600 hover:text-blue-800 inline-block" title="View">
                                        <Eye className="w-5 h-5" />
                                    </Link>
                                    <Link href={`/admin/courses/${course.id}/edit`} className="text-indigo-600 hover:text-indigo-800 inline-block" title="Edit">
                                        <Edit className="w-5 h-5" />
                                    </Link>
                                    <button onClick={() => handleDelete(course)} className="text-red-600 hover:text-red-800 inline-block" title="Delete">
                                        <Trash2 className="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {courses.data.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-6 py-4 text-center text-gray-500">
                                    No courses found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
