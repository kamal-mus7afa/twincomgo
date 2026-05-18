<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function logActivityPermission(Request $request)
    {
        $query = User::query()->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->get();
        return view(
            'admin.permission-log-activity',
            compact('users')
        );
    }

    public function index()
    {
        $permissions = Permission::all();
        return view('admin.permission.index', compact('permissions'));
    }

    public function edit($id) 
    {
        $user = User::findOrFail($id);
        $permissions = Permission::all();

        $groupedPermissions = [];

        foreach ($permissions as $permission) {
            [$action, $module] = explode('_', $permission->name);

            $groupedPermissions[$module][] = [
                'name' => $permission->name,
                'action' => $action
            ];
        }

        return view('admin.permission.edit', compact('user', 'groupedPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'actions' => 'required|array'
        ]);

        foreach ($request->actions as $action) {
            $name = $action . '_' . $request->module;

            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }
        
        Alert::success(
            'Berhasil',
            'Akses berhasil ditambahkan'
        );
        return back();
    }

    public function create()
    {
        return view('admin.permission.create');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->syncPermissions($request->permissions ?? []);

        toast('Akses diperbarui','success');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        $permission->delete();

        toast('Akses berhasil dihapus', 'success');
        return back();
    }
}
