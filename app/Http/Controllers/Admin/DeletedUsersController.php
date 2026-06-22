<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletedUser;
use Illuminate\Http\Request;

class DeletedUsersController extends Controller
{
    public function index()
    {
        $deletedUsers = DeletedUser::latest()->get();
        return view('admin.deleted-users.index', compact('deletedUsers'));
    }

    public function show(DeletedUser $deletedUser)
    {
        return view('admin.deleted-users.show', compact('deletedUser'));
    }

    public function destroy(DeletedUser $deletedUser)
    {
        $deletedUser->delete();
        flash()->success('Deleted user record removed successfully.');
        return redirect()->route('deleted-users.index');
    }
}
