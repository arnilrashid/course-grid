<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(private UserService $userService, private RoleService $roleService)
    {
    }

    public function index(Request $request)
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
            'coursesCount' => \Illuminate\Support\Facades\DB::table('courses')->where('user_id', $user->id)->count(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $this->userService->updateProfile($user, $validated);

        return back()->with('success', 'User profile updated successfully.');
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $this->userService->updateRole($user, $validated['role']);

        return back()->with('success', 'User role updated successfully.');
    }

    public function suspend(Request $request, User $user)
    {
        try {
            $this->userService->suspend($user, $request->input('reason'));
            return back()->with('success', 'User suspended successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function unsuspend(Request $request, User $user)
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

    public function destroy(Request $request, User $user)
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
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', 'You cannot impersonate a super admin.');
        }

        // Store original admin ID in session
        session()->put('impersonated_by', auth()->id());
        
        auth()->login($user);

        return redirect()->route('dashboard')->with('success', "You are now impersonating {$user->name}.");
    }

    public function stopImpersonating()
    {
        if (!session()->has('impersonated_by')) {
            return back();
        }

        $adminId = session()->pull('impersonated_by');
        $admin = User::find($adminId);

        if ($admin) {
            auth()->login($admin);
            return redirect()->route('admin.users.index')->with('success', 'Welcome back to your admin account.');
        }

        return redirect()->route('login');
    }
}
