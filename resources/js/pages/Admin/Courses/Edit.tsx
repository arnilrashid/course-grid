import AdminLayout from '@/pages/Admin/Layout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import CourseForm from './CourseForm';

export default function CourseEdit({ course, categories, instructors, statuses }: any) {
    const { data, setData, put, processing, errors } = useForm({
        title: course.title || '',
        slug: course.slug || '',
        description: course.description || '',
        price: course.price || '',
        language: course.language || 'en',
        category_id: course.category_id || '',
        user_id: course.user_id || '',
        status: course.status || 'draft',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/courses/${course.id}`);
    };

    return (
        <AdminLayout title="Edit Course">
            <Head title={`Admin - Edit ${course.title}`} />
            <CourseForm
                heading={`Edit Course: ${course.title}`}
                data={data}
                setData={setData}
                errors={errors}
                processing={processing}
                submitLabel="Save Changes"
                onSubmit={submit}
                categories={categories}
                instructors={instructors}
                statuses={statuses}
            />
        </AdminLayout>
    );
}
