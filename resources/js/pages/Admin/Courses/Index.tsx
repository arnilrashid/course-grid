import AdminLayout from '@/Pages/Admin/Layout';
import { Head, router } from '@inertiajs/react';
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
}

interface Props {
    courses: {
        data: Course[];
        links: any[];
    };
}

export default function CoursesIndex({ courses }: Props) {
    const handleApprove = (course: Course) => {
        if (!confirm(`Are you sure you want to approve "${course.title}"?`)) return;

        router.post(`/admin/courses/${course.id}/approve`, {}, {
            preserveScroll: true,
        });
    };

    const handleReject = (course: Course) => {
        const reason = prompt('Please provide a reason for rejecting this course:');
        if (reason === null) return;
        if (reason.trim() === '') {
            alert('A rejection reason is required.');
            return;
        }

        router.post(`/admin/courses/${course.id}/reject`, { reason }, {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout title="Course Moderation">
            <Head title="Admin - Courses" />

            <div className="bg-white rounded shadow-sm border overflow-x-auto">
                <table className="w-full text-left text-sm whitespace-nowrap">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="px-6 py-3 font-medium text-gray-500">Course</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Instructor</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Submitted</th>
                            <th className="px-6 py-3 font-medium text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {courses.data.map((course) => (
                            <tr key={course.id}>
                                <td className="px-6 py-4 font-medium text-gray-900">{course.title}</td>
                                <td className="px-6 py-4">
                                    {course.instructor?.name}
                                    <div className="text-xs text-gray-500">{course.instructor?.email}</div>
                                </td>
                                <td className="px-6 py-4">{new Date(course.created_at).toLocaleDateString()}</td>
                                <td className="px-6 py-4 text-right space-x-3">
                                    <button
                                        onClick={() => handleApprove(course)}
                                        className="text-green-600 hover:text-green-800 font-medium"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        onClick={() => handleReject(course)}
                                        className="text-red-600 hover:text-red-800 font-medium"
                                    >
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        ))}
                        {courses.data.length === 0 && (
                            <tr>
                                <td colSpan={4} className="px-6 py-4 text-center text-gray-500">
                                    No pending courses to review.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
