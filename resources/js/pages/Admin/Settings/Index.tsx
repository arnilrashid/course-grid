import AdminLayout from '@/Pages/Admin/Layout';
import { Head, router } from '@inertiajs/react';
import React, { useState } from 'react';

interface Setting {
    id: number;
    group: string;
    key: string;
    value: any;
    type: string;
    description: string;
}

interface Props {
    settings: Record<string, Setting[]>;
}

export default function SettingsIndex({ settings }: Props) {
    const [updatingId, setUpdatingId] = useState<number | null>(null);

    const handleUpdate = (e: React.FormEvent, setting: Setting) => {
        e.preventDefault();
        
        const form = e.target as HTMLFormElement;
        const input = form.elements.namedItem('value') as HTMLInputElement | HTMLSelectElement;
        
        setUpdatingId(setting.id);
        
        router.post('/admin/settings', {
            id: setting.id,
            value: input.value,
        }, {
            preserveScroll: true,
            onFinish: () => setUpdatingId(null),
        });
    };

    return (
        <AdminLayout title="Settings">
            <Head title="Admin - Settings" />

            <div className="space-y-8">
                {Object.entries(settings).map(([group, groupSettings]) => (
                    <div key={group} className="bg-white rounded shadow-sm border p-6">
                        <h3 className="text-lg font-medium mb-4 capitalize border-b pb-2">{group} Settings</h3>
                        
                        <div className="space-y-6">
                            {groupSettings.map((setting) => (
                                <form 
                                    key={setting.id} 
                                    onSubmit={(e) => handleUpdate(e, setting)}
                                    className="flex flex-col md:flex-row md:items-start gap-4 p-4 border rounded bg-gray-50"
                                >
                                    <div className="flex-1">
                                        <label className="block text-sm font-medium text-gray-700 capitalize">
                                            {setting.key.replace(/_/g, ' ')}
                                        </label>
                                        <p className="text-sm text-gray-500 mt-1">{setting.description}</p>
                                    </div>
                                    
                                    <div className="flex items-center gap-2">
                                        {setting.type === 'boolean' ? (
                                            <select 
                                                name="value" 
                                                defaultValue={setting.value?.toString() || "false"}
                                                className="border rounded px-3 py-2 text-sm"
                                            >
                                                <option value="true">True</option>
                                                <option value="false">False</option>
                                            </select>
                                        ) : (
                                            <input
                                                type={setting.type === 'integer' || setting.type === 'float' ? 'number' : 'text'}
                                                name="value"
                                                defaultValue={setting.value}
                                                step={setting.type === 'float' ? '0.01' : '1'}
                                                className="border rounded px-3 py-2 text-sm"
                                                required
                                            />
                                        )}
                                        
                                        <button 
                                            type="submit" 
                                            disabled={updatingId === setting.id}
                                            className="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 disabled:opacity-50"
                                        >
                                            {updatingId === setting.id ? 'Saving...' : 'Save'}
                                        </button>
                                    </div>
                                </form>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
