<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserController extends Controller
{
    // INDEX: List all users
    public function index()
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::latest()
                ->paginate(10)
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name, // Uses the accessor fallback
                    'email' => $user->email,
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'created_at' => $user->created_at,
                ]),
        ]);
    }

    // EDIT: Show the edit form
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user
        ]);
    }
public function create()
    {
        // This is what the error is looking for!
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'status' => 'required',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'username' => Str::before($validated['email'], '@') . '_' . Str::random(4),
            'is_active' => $validated['status'] === 'active',
        ]);

        return redirect()->route('admin.users.index')
            ->with('message', 'User created successfully.');
    }
    // UPDATE: Save changes
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_active' => 'required|boolean',
            'phone' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('message', 'User updated successfully');
    }
}