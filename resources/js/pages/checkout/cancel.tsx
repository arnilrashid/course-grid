export default function CheckoutCancel({ order }: { order: any }) {
    return (
        <div className="min-h-screen flex items-center justify-center">
            <div className="text-center">
                <h1 className="text-2xl font-bold mb-4">Payment Cancelled</h1>
                <p className="text-gray-600">Your payment was not completed. No charges were made.</p>
            </div>
        </div>
    );
}
