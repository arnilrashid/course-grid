import AdminLayout from '@/pages/Admin/Layout';
import { Head, Link } from '@inertiajs/react';
import React from 'react';
import { ArrowLeft, Edit } from 'lucide-react';

export default function CourseShow({ course }: any) {
    return (
        <AdminLayout title="Course Details">
            <Head title={`Admin - ${course.title}`} />

            <div className="mb-6 flex justify-between items-center">
                <div className="flex items-center space-x-4">
                    <Link href="/admin/courses" className="text-gray-500 hover:text-gray-700">
                        <ArrowLeft className="w-6 h-6" />
                    </Link>
                    <h1 className="text-2xl font-semibold text-gray-900">{course.title}</h1>
                </div>
                <Link 
                    href={`/admin/courses/${course.id}/edit`} 
                    className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    <Edit className="w-4 h-4 mr-2" />
                    Edit Course
                </Link>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="md:col-span-2 space-y-6">
                    <div className="bg-white rounded shadow-sm border p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">Course Details</h2>
                        <dl className="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Instructor</dt>
                                <dd className="mt-1 text-sm text-gray-900">{course.instructor?.name} ({course.instructor?.email})</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Category</dt>
                                <dd className="mt-1 text-sm text-gray-900">{course.category?.name || 'None'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Price</dt>
                                <dd className="mt-1 text-sm text-gray-900">${course.price}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Status</dt>
                                <dd className="mt-1 text-sm text-gray-900 capitalize">{course.status.replace('_', ' ')}</dd>
                            </div>
                            <div className="sm:col-span-2">
                                <dt className="text-sm font-medium text-gray-500">Description</dt>
                                <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{course.description || 'No description provided.'}</dd>
                            </div>
                        </dl>
                    </div>

                    <div className="bg-white rounded shadow-sm border p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">Curriculum Overview</h2>
                        {course.sections && course.sections.length > 0 ? (
                            <div className="space-y-4">
                                {course.sections.map((section: any) => (
                                    <div key={section.id} className="border rounded p-4">
                                        <h3 className="font-semibold">{section.position}. {section.title}</h3>
                                        <ul className="mt-2 ml-4 list-disc text-sm text-gray-600">
                                            {section.lessons && section.lessons.length > 0 ? (
                                                section.lessons.map((lesson: any) => (
                                                    <li key={lesson.id}>
                                                        {lesson.title} ({lesson.type})
                                                        {lesson.is_free_preview && <span className="ml-2 text-xs text-indigo-600 font-bold">[Preview]</span>}
                                                    </li>
                                                ))
                                            ) : (
                                                <li className="text-gray-400 italic">No lessons in this section.</li>
                                            )}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-gray-500 text-sm">No curriculum has been added yet.</p>
                        )}
                    </div>
                </div>

                <div className="space-y-6">
                    <div className="bg-white rounded shadow-sm border p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">Meta Data</h2>
                        <dl className="space-y-4">
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Created At</dt>
                                <dd className="mt-1 text-sm text-gray-900">{new Date(course.created_at).toLocaleString()}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd className="mt-1 text-sm text-gray-900">{new Date(course.updated_at).toLocaleString()}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Slug</dt>
                                <dd className="mt-1 text-sm text-gray-900 font-mono text-xs break-all">{course.slug}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
