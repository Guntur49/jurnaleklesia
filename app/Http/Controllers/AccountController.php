<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('accounts.index', [
            'users' => User::whereIn('role', ['staff', 'owner'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);
        User::create($attributes);

        return redirect()->route('accounts.index')->with('status', 'Akun berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureManagedAccount($user);
        $attributes = $this->validatedAttributes($request, $user);

        if (blank($attributes['password'] ?? null)) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return redirect()->route('accounts.index')->with('status', 'Informasi akun berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureManagedAccount($user);
        $user->delete();

        return redirect()->route('accounts.index')->with('status', 'Akun berhasil dihapus.');
    }

    private function validatedAttributes(Request $request, ?User $user = null): array
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user)],
            'role' => ['required', Rule::in(['staff', 'owner'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'journal_start_time' => ['nullable', 'date_format:H:i'],
            'journal_end_time' => ['nullable', 'date_format:H:i'],
        ]);

        if ($attributes['role'] === 'staff') {
            if (empty($attributes['journal_start_time']) || empty($attributes['journal_end_time'])) {
                throw ValidationException::withMessages([
                    'journal_start_time' => 'Jam mulai dan jam berakhir wajib diisi untuk akun staff.',
                ]);
            }

            if ($attributes['journal_start_time'] === $attributes['journal_end_time']) {
                throw ValidationException::withMessages([
                    'journal_end_time' => 'Jam mulai dan jam berakhir tidak boleh sama.',
                ]);
            }
        } else {
            $attributes['journal_start_time'] = null;
            $attributes['journal_end_time'] = null;
        }

        return $attributes;
    }

    private function ensureManagedAccount(User $user): void
    {
        abort_unless(in_array($user->role, ['staff', 'owner'], true), 404);

        if (auth()->id() === $user->id) {
            throw ValidationException::withMessages([
                'account' => 'Akun yang sedang digunakan tidak dapat dihapus atau diubah dari halaman ini.',
            ]);
        }
    }
}
