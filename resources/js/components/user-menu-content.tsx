import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, Settings, Shield } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User, Auth } from '@/types';
import type { SharedData } from '@inertiajs/core';

type Props = {
    user: User;
};

export function UserMenuContent({ user }: Props) {
    const cleanup = useMobileNavigation();
    const { auth } = usePage<SharedData & { auth: Auth }>().props;

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                {auth.isAdmin && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer"
                            href="/admin"
                            prefetch
                            onClick={cleanup}
                        >
                            <Shield className="mr-2" />
                            Admin Dashboard
                        </Link>
                    </DropdownMenuItem>
                )}
                {auth.isInstructor && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer"
                            href="/instructor/dashboard"
                            prefetch
                            onClick={cleanup}
                        >
                            <Shield className="mr-2" />
                            Instructor Dashboard
                        </Link>
                    </DropdownMenuItem>
                )}
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={edit()}
                        prefetch
                        onClick={cleanup}
                    >
                        <Settings className="mr-2" />
                        Settings
                    </Link>
                </DropdownMenuItem>
                {auth.isImpersonating && (
                    <DropdownMenuItem asChild>
                        <Link
                            className="block w-full cursor-pointer text-amber-600 focus:text-amber-700"
                            href="/stop-impersonating"
                            method="post"
                            as="button"
                        >
                            <Shield className="mr-2 h-4 w-4" />
                            Return to Admin
                        </Link>
                    </DropdownMenuItem>
                )}
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    Log out
                </Link>
            </DropdownMenuItem>
        </>
    );
}
