<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $totalUsers  = User::count();
        $lawyerCount = User::where('role', 'lawyer')->count();
        $clientCount = User::where('role', 'client')->count();
        $adminCount  = User::where('role', 'admin')->count();

        $users = User::withCount([
            'lawyerCases as cases_count',
            'clientCases as client_cases_count',
        ])->with('assignedLawyer:id,name')->latest()->get();

        $lawyers = User::where('role', 'lawyer')
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('admin.users', compact(
            'totalUsers', 'lawyerCount', 'clientCount', 'adminCount', 'users', 'lawyers'
        ));
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $users = User::withCount([
            'lawyerCases as cases_count',
            'clientCases as client_cases_count',
        ])->latest()->get();

        $pdf = Pdf::loadView('admin.users-pdf', compact('users'));

        return $pdf->download('admin-users.pdf');
    }

    public function export(): \Symfony\Component\HttpFoundation\Response
    {
        $users = User::withCount([
            'lawyerCases as cases_count',
            'clientCases as client_cases_count',
        ])->latest()->get();

        $pdf = Pdf::loadView('admin.users-pdf', compact('users'));

        return $pdf->download('admin-users.pdf');
    }

    public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'role'     => 'required|in:client,lawyer,admin',
        'phone'    => 'nullable|string|max:20',
        'password' => 'required|string|min:8|confirmed',
    ]);

    User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'role'      => $request->role,
        'phone'     => $request->phone,
        'password'  => bcrypt($request->password),
        'is_active' => true,
    ]);

    return redirect()->back()->with('success', 'User created successfully.');
}

    public function show(User $user): JsonResponse
    {
        $user->load(['lawyerCases', 'clientCases', 'billingRates']);

        return response()->json($user);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $old  = $user->toArray();
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        $this->auditLog('updated_user', $user, $old, $user->fresh()->toArray());

        return response()->json([
            'message' => 'User updated successfully.',
            'user'    => $user->fresh(),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself.'], 403);
        }

        $this->auditLog('deleted_user', $user);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        $this->auditLog("user_{$status}", $user);

        return response()->json([
            'message'   => "User {$status} successfully.",
            'is_active' => $user->is_active,
        ]);
    }

    public function assignLawyer(User $user): RedirectResponse
    {
        request()->validate([
            'lawyer_id' => 'required|exists:users,id',
        ]);

        $lawyer = User::where('id', request('lawyer_id'))
            ->where('role', 'lawyer')
            ->where('is_active', true)
            ->firstOrFail();

        $oldLawyerId = $user->lawyer_id;
        $user->update(['lawyer_id' => $lawyer->id]);

        $this->auditLog('lawyer_assigned', $user, ['lawyer_id' => $oldLawyerId], ['lawyer_id' => $lawyer->id]);

        return redirect()->back()->with('success', "Lawyer successfully assigned to {$user->name}");
    }
}