<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // PENTING: Mengambil data user beserta detail tipe pelanggannya
        $query = User::with('customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        $users = $query->latest()->get();
        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function update(Request $request, User $user_management): JsonResponse
    {
        if ($request->user()->role !== Role::Owner && $user_management->role === Role::Owner) {
            return response()->json(['message' => 'Dilarang mengubah data Pimpinan.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user_management->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user_management->update($data);
        return response()->json($user_management->fresh());
    }

    public function destroy(Request $request, User $user_management): JsonResponse
    {
        if ($request->user()->role !== Role::Owner && $user_management->role === Role::Owner) {
            return response()->json(['message' => 'Dilarang menghapus data Pimpinan.'], 403);
        }

        $user_management->delete();
        return response()->json(['status' => 'deleted']);
    }
}