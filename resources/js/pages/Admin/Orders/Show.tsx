import AdminLayout from '@/Pages/Admin/Layout';
import { Head, Link } from '@inertiajs/react';
import React from 'react';

interface Props {
    order: any; // Using any here for brevity given the deep nesting, but could type explicitly
}

export default function OrderShow({ order }: Props) {
    const formatMoney = (amount: string, currency: string) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency.toUpperCase(),
        }).format(Number(amount));
    };

    return (
        <AdminLayout title={`Order #${order.id} Pipeline`}>
            <Head title={`Admin - Order #${order.id}`} />

            <div className="mb-4">
                <Link href="/admin/orders" className="text-sm text-blue-600 hover:underline">
                    &larr; Back to Orders
                </Link>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-white rounded shadow-sm border p-6">
                    <h3 className="text-lg font-medium mb-4 border-b pb-2">Order Details</h3>
                    <div className="space-y-2 text-sm">
                        <div className="flex justify-between"><span className="text-gray-500">Status:</span> <span>{order.status}</span></div>
                        <div className="flex justify-between"><span className="text-gray-500">Customer:</span> <span>{order.user?.name} ({order.user?.email})</span></div>
                        <div className="flex justify-between"><span className="text-gray-500">Total:</span> <span>{formatMoney(order.total, order.currency)}</span></div>
                        <div className="flex justify-between"><span className="text-gray-500">Created:</span> <span>{new Date(order.created_at).toLocaleString()}</span></div>
                        <div className="flex justify-between"><span className="text-gray-500">Idempotency Key:</span> <span className="font-mono text-xs">{order.idempotency_key}</span></div>
                    </div>

                    <h4 className="font-medium mt-6 mb-2">Items</h4>
                    <ul className="list-disc pl-5 text-sm space-y-1">
                        {order.items?.map((item: any) => (
                            <li key={item.id}>{item.course?.title} - {formatMoney(item.price, item.currency)}</li>
                        ))}
                    </ul>
                </div>

                <div className="bg-white rounded shadow-sm border p-6 overflow-hidden">
                    <h3 className="text-lg font-medium mb-4 border-b pb-2">Payment Pipeline</h3>
                    
                    {order.payment_attempts?.map((attempt: any, i: number) => (
                        <div key={attempt.id} className="mb-6 last:mb-0 border-l-2 border-slate-300 pl-4">
                            <h4 className="font-semibold text-slate-800">Attempt #{i+1}</h4>
                            <div className="text-sm space-y-1 mt-2">
                                <div><span className="text-gray-500">Provider:</span> {attempt.provider}</div>
                                <div><span className="text-gray-500">Provider ID:</span> <span className="font-mono text-xs">{attempt.provider_payment_id || 'N/A'}</span></div>
                                <div><span className="text-gray-500">Status:</span> {attempt.status}</div>
                                {attempt.error_information && (
                                    <div className="text-red-600 mt-1 p-2 bg-red-50 rounded font-mono text-xs overflow-x-auto whitespace-pre-wrap">
                                        {JSON.stringify(attempt.error_information, null, 2)}
                                    </div>
                                )}
                            </div>

                            {attempt.payment_transaction && (
                                <div className="mt-4 bg-slate-50 p-3 rounded text-sm">
                                    <h5 className="font-medium mb-2">Transaction</h5>
                                    <div><span className="text-gray-500">Provider ID:</span> <span className="font-mono text-xs">{attempt.payment_transaction.provider_transaction_id}</span></div>
                                    <div><span className="text-gray-500">Status:</span> {attempt.payment_transaction.status}</div>
                                    
                                    {attempt.payment_transaction.refunds?.length > 0 && (
                                        <div className="mt-3 pt-3 border-t border-slate-200">
                                            <h6 className="font-medium text-orange-700">Refunds</h6>
                                            {attempt.payment_transaction.refunds.map((refund: any) => (
                                                <div key={refund.id} className="mt-1">
                                                    Refunded {formatMoney(refund.amount, refund.currency)} - Status: {refund.status}
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    ))}
                    {(!order.payment_attempts || order.payment_attempts.length === 0) && (
                        <div className="text-sm text-gray-500">No payment attempts recorded.</div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
