<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Mail\AdminPasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminMemberController extends Controller
{
    public function index()
    {
        $members = AdminUser::orderBy('created_at', 'desc')->get();

        return view('backoffice.admin-members.index', compact('members'));
    }

    public function create()
    {
        return view('backoffice.admin-members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'role' => 'required|in:administrator,team_member',
            'permissions' => 'array',
        ]);

        // Generate random password
        $password = Str::random(12);

        $member = AdminUser::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'permissions' => $validated['role'] === 'team_member' ? $this->processPermissions($request) : null,
            'status' => 'active',
            'must_change_password' => true,
        ]);

        // Send welcome email with password
        Mail::to($member->email)->send(new AdminPasswordResetMail($member, $password));

        return redirect()->route('backoffice.admin-members.index')
            ->with('success', 'Admin member invited successfully. Login credentials sent to ' . $member->email);
    }

    public function edit(AdminUser $adminMember)
    {
        return view('backoffice.admin-members.edit', compact('adminMember'));
    }

    public function update(Request $request, AdminUser $adminMember)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $adminMember->id,
            'role' => 'required|in:administrator,team_member',
            'permissions' => 'array',
        ]);

        $adminMember->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'permissions' => $validated['role'] === 'team_member' ? $this->processPermissions($request) : null,
        ]);

        return redirect()->route('backoffice.admin-members.index')
            ->with('success', 'Admin member updated successfully.');
    }

    public function destroy(AdminUser $adminMember)
    {
        // Prevent self-deletion
        if ($adminMember->id === auth('admin')->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $adminMember->delete();

        return redirect()->route('backoffice.admin-members.index')
            ->with('success', 'Admin member deleted successfully.');
    }

    public function toggleStatus(AdminUser $adminMember)
    {
        // Prevent self-suspension
        if ($adminMember->id === auth('admin')->id()) {
            return redirect()->back()
                ->with('error', 'You cannot suspend your own account.');
        }

        $newStatus = $adminMember->status === 'active' ? 'suspended' : 'active';
        $adminMember->update(['status' => $newStatus]);

        $action = $newStatus === 'active' ? 'reactivated' : 'suspended';

        return redirect()->back()
            ->with('success', "Admin member {$action} successfully.");
    }

    public function resetPassword(AdminUser $adminMember)
    {
        // Generate new random password
        $password = Str::random(12);

        $adminMember->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        // Send email with new password
        Mail::to($adminMember->email)->send(new AdminPasswordResetMail($adminMember, $password));

        return redirect()->back()
            ->with('success', 'Password reset and sent to ' . $adminMember->email);
    }

    protected function processPermissions(Request $request): array
    {
        return [
            'dashboard' => $request->boolean('perm_dashboard'),
            'clients' => $request->boolean('perm_clients'),
            'plans' => $request->boolean('perm_plans'),
            'email_templates' => $request->boolean('perm_email_templates'),
            'email_logs' => $request->boolean('perm_email_logs'),
            'admin_members' => $request->boolean('perm_admin_members'),
            'settings' => $request->boolean('perm_settings'),
        ];
    }
}
