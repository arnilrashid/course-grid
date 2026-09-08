import { useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

export type Props = {
    socialIdentities: string[];
};

export default function ManageConnectedAccounts({ socialIdentities }: Props) {
    const isGoogleConnected = socialIdentities.includes('google');
    const { delete: destroy } = useForm();

    const handleConnectGoogle = () => {
        window.location.href = '/auth/google';
    };

    const handleDisconnectGoogle = () => {
        destroy('/auth/google/unlink', {
            preserveScroll: true,
        });
    };

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="Connected Accounts"
                description="Manage your connected social accounts"
            />

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Google</CardTitle>
                    <CardDescription>
                        Sign in to CourseGrid using your Google account.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {isGoogleConnected ? (
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-medium text-green-600">
                                Connected
                            </span>
                            <Button
                                variant="destructive"
                                onClick={handleDisconnectGoogle}
                            >
                                Disconnect
                            </Button>
                        </div>
                    ) : (
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-medium text-muted-foreground">
                                Not connected
                            </span>
                            <Button
                                variant="outline"
                                onClick={handleConnectGoogle}
                            >
                                Connect Google
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
