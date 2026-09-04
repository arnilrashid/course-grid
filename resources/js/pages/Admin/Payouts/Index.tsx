import AdminLayout from '@/Pages/Admin/Layout';
import { Head, router } from '@inertiajs/react';
import React from 'react';

interface Earning {
    id: number;
    amount: string;
    source_type: string;
}

interface Payout {
    id: number;
    amount: string;
    status: string;
    period_start: string;
    period_end: string;
    paid_at: string | null;
    instructor?: {
        name: string;
        email: string;
    };
    earnings?: Earning[];
}

interface Props {
    payouts: {
        data: Payout[];
        links: any[];
    };
}

export default function PayoutsIndex({ payouts }: Props) {
    const handleMarkPaid = (payout: Payout) => {
        if (!confirm('Mark this payout as paid? This cannot be undone.')) return;

        router.post(`/admin/payouts/${payout.id}/mark-paid`, {}, {
            preserveScroll: true,
        });
    };

    const formatMoney = (amount: string) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(Number(amount));
    };

    return (
        <AdminLayout title="Payouts">
            <Head title="Admin - Payouts" />

            <div className="bg-white rounded shadow-sm border overflow-x-auto">
                <table className="w-full text-left text-sm whitespace-nowrap">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="px-6 py-3 font-medium text-gray-500">Instructor</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Period</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Earnings</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Amount</th>
                            <th className="px-6 py-3 font-medium text-gray-500">Status</th>
                            <th className="px-6 py-3 font-medium text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {payouts.data.map((payout) => (
                            <tr key={payout.id}>
                                <td className="px-6 py-4">
                                    {payout.instructor?.name}
                                    <div className="text-xs text-gray-500">{payout.instructor?.email}</div>
                                </td>
                                <td className="px-6 py-4">
                                    {new Date(payout.period_start).toLocaleDateString()} - {new Date(payout.period_end).toLocaleDateString()}
                                </td>
                                <td className="px-6 py-4">
                                    <details className="text-xs">
                                        <summary className="cursor-pointer text-blue-600">View {payout.earnings?.length || 0} items</summary>
                                        <div className="mt-2 space-y-1 bg-gray-50 p-2 rounded max-h-32 overflow-y-auto">
                                            {payout.earnings?.map(e => (
                                                <div key={e.id}>{e.source_type}: {formatMoney(e.amount)}</div>
                                            ))}
                                        </div>
                                    </details>
                                </td>
                                <td className="px-6 py-4 font-medium">{formatMoney(payout.amount)}</td>
                                <td className="px-6 py-4">
                                    {payout.status === 'paid' ? (
                                        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Paid on {new Date(payout.paid_at!).toLocaleDateString()}
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {payout.status}
                                        </span>
                                    )}
                                </td>
                                <td className="px-6 py-4 text-right">
                                    {payout.status !== 'paid' && (
                                        <button
                                            onClick={() => handleMarkPaid(payout)}
                                            className="text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            Mark Paid
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {payouts.data.length === 0 && (
                            <tr>
                                <td colSpan={6} className="px-6 py-4 text-center text-gray-500">
                                    No payouts found.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
