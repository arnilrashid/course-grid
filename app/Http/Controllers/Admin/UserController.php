<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\Admin\UserService;
use App\Services\Admin\RoleService;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private RoleService $roleService)
    {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => $this->userService->getUsers($request->input('search'), $request->input('role')),
            'roles' => $this->roleService->getAllRoles(),
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load('roles', 'permissions'),
            'activity' => $this->userService->getUserActivity($user),
            'sessions' => $this->userService->getUserSessions($user),
            'availableRoles' => $this->roleService->getAllRoles(),
            'coursesCount' => $user->courses()->count(),
            'has_2fa' => !is_null($user->two_factor_secret),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->updateProfile($user, $request->validated());

        return back()->with('success', 'User profile updated successfully.');
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        $this->userService->updateRole($user, $request->validated()['role']);

        return back()->with('success', 'User role updated successfully.');
    }

    public function suspend(\Illuminate\Http\Request $request, User $user)
    {
        try {
            $this->userService->suspend($user, $request->input('reason'));
            return back()->with('success', 'User suspended successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function unsuspend(\Illuminate\Http\Request $request, User $user)
    {
        $this->userService->unsuspend($user, $request->input('reason'));
        
        return back()->with('success', 'User unsuspended successfully.');
    }

    public function revokeSessions(User $user)
    {
        $this->userService->revokeSessions($user);
        
        return back()->with('success', 'User sessions revoked successfully.');
    }

    public function revokeSession(User $user, string $sessionId)
    {
        $this->userService->revokeSession($user, $sessionId);
        
        return back()->with('success', 'Session revoked successfully.');
    }

    public function destroy(\Illuminate\Http\Request $request, User $user)
    {
        try {
            $transferToUserId = $request->input('transfer_to_user_id');
            $transferToUser = null;
            
            if ($transferToUserId) {
                $transferToUser = User::findOrFail($transferToUserId);
            }

            $this->userService->deleteUser($user, $transferToUser);
            
            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function impersonate(User $user)
    {
        try {
            $this->userService->impersonate($user);
            return redirect()->route('dashboard')->with('success', "You are now impersonating {$user->name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function stopImpersonating()
    {
        $admin = $this->userService->stopImpersonating();

        if ($admin) {
            auth()->login($admin);
            return redirect()->route('admin.users.index')->with('success', 'Welcome back to your admin account.');
        }

        return redirect()->route('login');
    }

    public function verifyEmail(User $user)
    {
        try {
            $this->userService->verifyEmail($user);
            return back()->with('success', 'User email verified successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function resendVerificationEmail(User $user)
    {
        try {
            $this->userService->resendVerificationEmail($user);
            return back()->with('success', 'Verification email sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function disableTwoFactor(User $user)
    {
        try {
            $this->userService->disableTwoFactor($user);
            return back()->with('success', 'Two-Factor Authentication disabled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
