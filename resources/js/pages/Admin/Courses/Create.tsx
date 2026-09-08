import AdminLayout from '@/pages/Admin/Layout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import CourseForm from './CourseForm';

export default function CourseCreate({ categories, instructors, statuses }: any) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        slug: '',
        description: '',
        price: '',
        language: 'en',
        category_id: '',
        user_id: '',
        status: 'draft',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/courses');
    };

    return (
        <AdminLayout title="Create Course">
            <Head title="Admin - Create Course" />
            <CourseForm
                heading="Create New Course"
                data={data}
                setData={setData}
                errors={errors}
                processing={processing}
                submitLabel="Create Course"
                onSubmit={submit}
                categories={categories}
                instructors={instructors}
                statuses={statuses}
            />
        </AdminLayout>
    );
}
