@extends('layouts.app')

@section('title', 'Kelola akun')

@section('content')
    <div class="page-head">
        <div><p class="eyebrow">Administrasi pengguna</p><h1>Kelola akun</h1><p class="lead">Buat dan perbarui akses staff serta owner.</p></div>
    </div>
    <section class="card">
        <p class="eyebrow">Akun baru</p>
        <h2>Buat akun staff atau owner</h2>
        <form method="POST" action="{{ route('accounts.store') }}" style="margin-top:20px">
            @csrf
            <div class="form-grid">
                <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" value="{{ old('name') }}" required></div>
                <div class="field"><label for="position">Jabatan</label><input id="position" name="position" value="{{ old('position') }}" placeholder="Contoh: Administrasi" required></div>
                <div class="field"><label for="username">Username</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="off" required></div>
                <div class="field"><label for="role">Peran</label><select id="role" name="role" required><option value="staff" @selected(old('role', 'staff') === 'staff')>Staff</option><option value="owner" @selected(old('role') === 'owner')>Owner</option></select></div>
                <div class="field"><label for="password">Kata sandi awal</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
                <div class="field schedule-field"><label for="journal_start_time">Mulai waktu pengisian jurnal</label><input id="journal_start_time" name="journal_start_time" type="time" value="{{ old('journal_start_time', '16:00') }}"></div>
                <div class="field schedule-field"><label for="journal_end_time">Batas akhir pengisian</label><input id="journal_end_time" name="journal_end_time" type="time" value="{{ old('journal_end_time', '18:00') }}"></div>
            </div>
            <p class="muted small">Jam pengisian hanya berlaku untuk staff. Untuk jadwal yang melewati tengah malam, isi waktu mulai lebih akhir daripada waktu selesai.</p>
            <div class="form-actions"><button class="btn" type="submit">Buat akun</button></div>
        </form>
    </section>

    <section class="card section">
        <div class="report-meta"><div><p class="eyebrow">Akses terdaftar</p><h2 class="section-title" style="margin:0">Staff dan owner</h2></div><span class="badge">{{ $users->count() }} akun</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama</th><th>Username</th><th>Peran / jabatan</th><th>Waktu jurnal</th><th>Pengaturan</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="table-name">{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td><span class="badge {{ $user->role === 'owner' ? 'badge-orange' : '' }}">{{ ucfirst($user->role) }}</span><div class="small muted" style="margin-top:6px">{{ $user->position }}</div></td>
                            <td>{{ $user->role === 'staff' ? substr($user->journal_start_time, 0, 5).'–'.substr($user->journal_end_time, 0, 5) : '—' }}</td>
                            <td>
                                <details class="details">
                                    <summary>Edit akun</summary>
                                    <form method="POST" action="{{ route('accounts.update', $user) }}" style="margin-top:16px">
                                        @csrf @method('PUT')
                                        <div class="form-grid">
                                            <div class="field"><label>Nama</label><input name="name" value="{{ $user->name }}" required></div>
                                            <div class="field"><label>Jabatan</label><input name="position" value="{{ $user->position }}" required></div>
                                            <div class="field"><label>Username</label><input name="username" value="{{ $user->username }}" required></div>
                                            <div class="field"><label>Peran</label><select name="role" required><option value="staff" @selected($user->role === 'staff')>Staff</option><option value="owner" @selected($user->role === 'owner')>Owner</option></select></div>
                                            <div class="field"><label>Kata sandi baru (opsional)</label><input name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tidak diubah"></div>
                                            <div class="field schedule-field"><label>Mulai pengisian jurnal</label><input name="journal_start_time" type="time" value="{{ $user->journal_start_time ? substr($user->journal_start_time, 0, 5) : '' }}"></div>
                                            <div class="field schedule-field"><label>Batas akhir pengisian</label><input name="journal_end_time" type="time" value="{{ $user->journal_end_time ? substr($user->journal_end_time, 0, 5) : '' }}"></div>
                                        </div>
                                        <div class="form-actions"><button class="btn btn-small" type="submit">Simpan perubahan</button></div>
                                    </form>
                                    <form method="POST" action="{{ route('accounts.destroy', $user) }}" onsubmit="return confirm('Hapus akun {{ $user->name }} beserta jurnalnya?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-small" type="submit">Hapus akun</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">Belum ada akun staff atau owner.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <style>.schedule-field{transition:opacity .15s}.report-meta{margin-top:0}</style>
    <script>
        document.querySelectorAll('select[name="role"]').forEach(function(select){function toggle(){var form=select.closest('form');if(!form)return;form.querySelectorAll('.schedule-field').forEach(function(field){field.style.opacity=select.value==='staff'?'1':'.45';field.querySelector('input').disabled=select.value!=='staff';});}select.addEventListener('change',toggle);toggle();});
    </script>
@endsection
