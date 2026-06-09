<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $query = User::query();

        if ($authUser && $authUser->role !== Role::Owner) {
            $query->where('role', '!=', Role::Owner);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($request->sort_by, $sortOrder);
        } else {
            $query->latest();
        }

        $users = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $allowedRoles = $this->allowedRoles($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer', Rule::in(array_map(fn (Role $r) => $r->value, $allowedRoles))],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    public function show(Request $request, User $user_management): JsonResponse
    {
        $authUser = $request->user();
        if ($authUser && $authUser->role !== Role::Owner && $user_management->role === Role::Owner) {
            abort(403, 'Unauthorized access to owner user data');
        }

        return response()->json($user_management);
    }

    public function update(Request $request, User $user_management): JsonResponse
    {
        $authUser = $request->user();
        if ($authUser && $authUser->role !== Role::Owner && $user_management->role === Role::Owner) {
            abort(403, 'Unauthorized modification of owner user data');
        }

        $allowedRoles = $this->allowedRoles($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user_management->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer', Rule::in(array_map(fn (Role $r) => $r->value, $allowedRoles))],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user_management->update($data);

        return response()->json($user_management->fresh());
    }

    public function destroy(Request $request, User $user_management): JsonResponse
    {
        $authUser = $request->user();
        if ($authUser && $authUser->role !== Role::Owner && $user_management->role === Role::Owner) {
            abort(403, 'Unauthorized deletion of owner user data');
        }

        $user_management->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Return the roles the authenticated user is allowed to assign.
     *
     * @return Role[]
     */
    private function allowedRoles(Request $request): array
    {
        $authUser = $request->user();

        if ($authUser && $authUser->role === Role::Owner) {
            return [Role::Owner, Role::Admin, Role::Writer];
        }

        // Admin can only assign Admin and Writer
        return [Role::Admin, Role::Writer];
    }
}
