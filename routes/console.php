<?php

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:create-admin', function () {
    if (User::where('role', 'admin')->exists()) {
        $this->error('Akun admin sudah tersedia.');

        return Command::FAILURE;
    }

    $name = $this->ask('Nama admin');
    $username = $this->ask('Username admin');
    $password = $this->secret('Kata sandi admin (minimal 12 karakter)');
    $confirmation = $this->secret('Ulangi kata sandi');

    if ($password !== $confirmation) {
        $this->error('Konfirmasi kata sandi tidak cocok.');

        return Command::FAILURE;
    }

    $validator = Validator::make([
        'name' => $name,
        'username' => $username,
        'password' => $password,
    ], [
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:100', 'unique:users,username'],
        'password' => ['required', 'string', 'min:12', 'max:255'],
    ]);

    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return Command::FAILURE;
    }

    User::create([
        'name' => $name,
        'username' => $username,
        'position' => 'Administrator',
        'role' => 'admin',
        'password' => $password,
    ]);

    $this->info('Akun admin berhasil dibuat. Simpan kredensial di tempat yang aman.');

    return Command::SUCCESS;
})->purpose('Buat akun admin pertama secara interaktif');
