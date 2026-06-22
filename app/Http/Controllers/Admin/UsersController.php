<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AccountType;
use App\Models\OfferType;
use App\Models\UserType;
use App\Models\Country;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'account_type', 'user_type'])->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles        = Role::all();
        $accountTypes = AccountType::all();
        $offerTypes   = OfferType::all();
        $userTypes    = UserType::all();
        return view('admin.users.add', compact('roles', 'accountTypes', 'offerTypes', 'userTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|min:8|confirmed',
            'role_id'         => 'required|exists:roles,id',
            'account_type_id' => 'nullable|exists:account_types,id',
            'offer_type_id'   => 'nullable|exists:offer_types,id',
            'user_type_id'    => 'nullable|exists:user_types,id',
            'phone'           => 'nullable|string|max:30',
        ]);

        User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => bcrypt($request->password),
            'role_id'         => $request->role_id,
            'account_type_id' => $request->account_type_id,
            'offer_type_id'   => $request->offer_type_id,
            'user_type_id'    => $request->user_type_id ?? 1,
            'phone'           => $request->phone,
            'profile_picture' => 'users/default.png',
        ]);

        flash()->success('User created successfully.');
        return redirect()->route('users.index');
    }

    public function show(User $user)
    {
        $user->load(['role', 'account_type', 'offer_type', 'user_type', 'country', 'state', 'wallet']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles        = Role::all();
        $accountTypes = AccountType::all();
        $offerTypes   = OfferType::all();
        $userTypes    = UserType::all();
        $countries    = Country::all();
        return view('admin.users.edit', compact('user', 'roles', 'accountTypes', 'offerTypes', 'userTypes', 'countries'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'role_id'         => 'required|exists:roles,id',
            'account_type_id' => 'nullable|exists:account_types,id',
            'offer_type_id'   => 'nullable|exists:offer_types,id',
            'user_type_id'    => 'nullable|exists:user_types,id',
            'phone'           => 'nullable|string|max:30',
            'service_charge'  => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->only([
            'name', 'email', 'role_id',
            'account_type_id', 'offer_type_id', 'user_type_id',
            'phone', 'service_charge',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        flash()->success('User updated successfully.');
        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        flash()->success('User deleted successfully.');
        return redirect()->route('users.index');
    }

    /**
     * Toggle disabled status for a user account.
     * POST admin/users/{user}/toggle-disable
     */
    public function toggleDisable(Request $request, User $user)
    {
        if ($user->disabled) {
            $user->update(['disabled' => false, 'disabled_reason' => null]);
            flash()->success('User account for "' . $user->name . '" has been re-enabled.');
        } else {
            $request->validate([
                'disabled_reason' => 'nullable|string|max:500',
            ]);
            $user->update([
                'disabled'        => true,
                'disabled_reason' => $request->disabled_reason ?? 'Account disabled by admin.',
            ]);
            flash()->warning('User account for "' . $user->name . '" has been disabled.');
        }
        return redirect()->route('users.index');
    }
}
