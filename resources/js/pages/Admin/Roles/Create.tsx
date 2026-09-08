import AdminLayout from '@/pages/Admin/Layout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import RoleForm, { Permission } from './RoleForm';

interface Props {
    permissions: Permission[];
}

export default function RolesCreate({ permissions }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        permissions: [] as string[]
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/roles');
    };

    return (
        <AdminLayout title="Create New Role">
            <Head title="Create Role - Admin" />
            <RoleForm
                title="Create New Role"
                description="Define a new system role and assign its permissions."
                submitLabel="Create Role"
                data={data}
                setData={setData}
                errors={errors}
                submitDisabled={processing || !data.name.trim()}
                onSubmit={submit}
                permissions={permissions}
            />
        </AdminLayout>
    );
}
