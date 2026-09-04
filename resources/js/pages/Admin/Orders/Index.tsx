import AdminLayout from '@/Pages/Admin/Layout';
import { Head, Link } from '@inertiajs/react';
import React from 'react';

interface Order {
    id: number;
    total: string;
    currency: string;
    status: string;
    created_at: string;
    user?: {
        name: string;
        email: string;
    };
    items?: {
        id: number;
        course?: {
            title: string;
        };
    }[];
}

interface Props {
    orders: {
        data: Order[];
        links: any[];
    };
}

export default function OrdersIndex({ orders }: Props) {
    const formatMoney = (amount: string, currency: string) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency.toUpperCase(),
        }).format(Number(amount));
    };

    return (
        <AdminLayout title="Orders & Payments">
            <Head title="Admin - Orders" />

            <div className="bg-white rounded shadow-sm border overflow-x-auto">
                <table className="w-full text-left text-sm whitespace-nowrap">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="px-6 py-3 font-medium text-gray-500">Order ID</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Customer</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Items</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Total</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Status</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Date</th>
                            <th className="px-6 py-3 font-medium text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {orders.data.map((order) => (
                            <tr key={order.id}>
                                <td className="px-6 py-4">#{order.id}</td>
                                <td className="px-6 py-4">
                                    {order.user?.name}
                                    <div className="text-xs text-gray-500">{order.user?.email}</div>
                                </td>
                                <td className="px-6 py-4">
                                    {order.items?.map((item) => (
                                        <div key={item.id} className="text-xs text-gray-600 truncate max-w-[200px]" title={item.course?.title}>
                                            {item.course?.title}
                                        </div>
                                    ))}
                                </td>
                                <td className="px-6 py-4 font-medium">
                                    {formatMoney(order.total, order.currency)}
                                </td>
                                <td className="px-6 py-4">
                                    <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {order.status}
                                    </span>
                                </td>
                                <td className="px-6 py-4">{new Date(order.created_at).toLocaleDateString()}</td>
                                <td className="px-6 py-4 text-right">
                                    <Link href={`/admin/orders/${order.id}`} className="text-blue-600 hover:underline">
                                        View Pipeline
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {orders.data.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-6 py-4 text-center text-gray-500">
                                    No orders found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
            
            <div className="mt-4 flex gap-2">
                {orders.links.map((link, i) => (
                    link.url && (
                        <Link
                            key={i}
                            href={link.url}
                            className={`px-3 py-1 rounded border text-sm ${link.active ? 'bg-slate-800 text-white' : 'bg-white hover:bg-gray-50'}`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    )
                ))}
            </div>
        </AdminLayout>
    );
}
