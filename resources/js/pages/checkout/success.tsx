export default function CheckoutSuccess({ order, fulfilled }: { order: any; fulfilled: boolean }) {
    return (
        <div className="min-h-screen flex items-center justify-center">
            <div className="text-center">
                <h1 className="text-2xl font-bold mb-4">
                    {fulfilled ? 'Payment Successful!' : 'Processing Payment...'}
                </h1>
                {!fulfilled && (
                    <p className="text-gray-600">Your payment is being processed. This page will update automatically.</p>
                )}
                {fulfilled && (
                    <p className="text-green-600">Your enrollment is confirmed. You can now access your course.</p>
                )}
            </div>
        </div>
    );
}
