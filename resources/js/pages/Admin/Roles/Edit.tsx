import AdminLayout from '@/pages/Admin/Layout';
import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import RoleForm, { Permission } from './RoleForm';

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    role: Role;
    permissions: Permission[];
}

export default function RolesEdit({ role, permissions }: Props) {
    const { data, setData, post, processing, isDirty, errors } = useForm({
        _method: 'PUT',
        name: role.name,
        permissions: role.permissions.map(p => p.name)
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/admin/roles/${role.id}`);
    };

    const isAdmin = role.name === 'admin';

    return (
        <AdminLayout title={`Edit Role: ${role.name}`}>
            <Head title={`Edit Role ${role.name} - Admin`} />
            <RoleForm
                title={`Edit ${role.name} Role`}
                description="Update the role's name and its assigned permissions."
                submitLabel="Save Changes"
                data={data}
                setData={setData}
                errors={errors}
                submitDisabled={processing || !isDirty || isAdmin}
                onSubmit={submit}
                permissions={permissions}
                isAdmin={isAdmin}
            />
        </AdminLayout>
    );
}
