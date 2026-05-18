<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kampus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PkkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pkks = User::with('kampus')->where('role', 'pkk')->get();
        return view('admin.pkk.index', compact('pkks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kampuses = Kampus::where('is_active', 1)->get();
        return view('admin.pkk.create', compact('kampuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'      => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'password'      => ['required', 'string', 'min:8'],
            'nama_lengkap'  => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'kampus_id'     => ['required', 'exists:kampus,kampus_id'],
            'angkatan'      => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'foto_profil'   => ['nullable', 'image', 'max:2048'],
        ]);

        $user = new User();
        $user->username      = $validated['username'];
        $user->password      = Hash::make($validated['password']);
        $user->nama_lengkap  = $validated['nama_lengkap'];
        $user->tanggal_lahir = $validated['tanggal_lahir'];
        $user->kampus_id     = $validated['kampus_id'];
        $user->angkatan      = $validated['angkatan'];
        $user->role          = 'pkk';
        $user->is_active     = 1;

        if ($request->hasFile('foto_profil')) {
            $path = $request->file('foto_profil')->store('foto_profil', 'public');
            $user->foto_profil = $path;
        }

        $user->save();

        return redirect()->route('pkk.index')->with('success', 'Profil PKK berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pkk = User::where('role', 'pkk')->findOrFail($id);
        $kampuses = Kampus::where('is_active', 1)->get();
        return view('admin.pkk.edit', compact('pkk', 'kampuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::where('role', 'pkk')->findOrFail($id);

        $validated = $request->validate([
            'username'      => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'password'      => ['nullable', 'string', 'min:8'],
            'nama_lengkap'  => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'kampus_id'     => ['required', 'exists:kampus,kampus_id'],
            'angkatan'      => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'foto_profil'   => ['nullable', 'image', 'max:2048'],
            'is_active'     => ['required', 'boolean'],
        ]);

        $user->username      = $validated['username'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->nama_lengkap  = $validated['nama_lengkap'];
        $user->tanggal_lahir = $validated['tanggal_lahir'];
        $user->kampus_id     = $validated['kampus_id'];
        $user->angkatan      = $validated['angkatan'];
        $user->is_active     = $validated['is_active'];

        if ($request->hasFile('foto_profil')) {
            $path = $request->file('foto_profil')->store('foto_profil', 'public');
            $user->foto_profil = $path;
        }

        $user->save();

        return redirect()->route('pkk.index')->with('success', 'Profil PKK berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::where('role', 'pkk')->findOrFail($id);
        $user->delete();

        return redirect()->route('pkk.index')->with('success', 'Profil PKK berhasil dihapus!');
    }
}
