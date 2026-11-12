<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class AuthController extends Controller
{
    // 1. Tampilkan Halaman Home
    public function home()
    {
        // Mengambil properti yang statusnya AVAILABLE (saat ini masih kosong di DB)
        $properties = DB::table('properties')->where('status', 'AVAILABLE')->get();
        return view('home', compact('properties'));
    }

    // 2. Tampilkan Halaman Login / Register / Login Agent
    public function showAuthForm()
    {
        return view('auth');
    }

    // 3. Proses Login (Mendukung pencarian email yang terenkripsi)
    public function login(Request $request)
    {
        $request->validate([
            'login'      => 'required|string',
            'password'   => 'required|string',
            'login_type' => 'required|in:user,agent',
        ]);

        // Buat hash SHA-256 dari input login (jika user memasukkan email)
        $loginHash = hash('sha256', strtolower(trim($request->login)));

        // Cari berdasarkan username ATAU email_hash
        $user = User::where('username', $request->login)
                    ->orWhere('email_hash', $loginHash)
                    ->first();

        if (!$user) {
            return back()->withErrors(['login' => 'Akun tidak ditemukan.'])->withInput();
        }

        if ($user->lockout_until && Carbon::now()->isBefore($user->lockout_until)) {
            $menit = Carbon::now()->diffInMinutes($user->lockout_until) + 1;
            return back()->withErrors(['login' => "Akun terkunci sementara. Coba lagi dalam {$menit} menit."]);
        }

        if ($user->status === 'SUSPENDED') {
            return back()->withErrors(['login' => 'Akun Anda sedang ditangguhkan.']);
        }

        if (Hash::check($request->password, $user->password_hash)) {
            if ($request->login_type === 'agent' && !$user->hasRole('AGENT')) {
                return back()->withErrors(['login' => 'Akses ditolak! Akun Anda bukan akun Agent resmi.']);
            }

            if ($request->login_type === 'user' && $user->hasRole('AGENT')) {
                return back()->withErrors(['login' => 'Akun Agent wajib masuk melalui tab "Login sebagai Agent".']);
            }

            $user->update(['failed_login_attempts' => 0, 'lockout_until' => null]);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Selamat datang kembali, ' . $user->username . '!');
        }

        $percobaan = $user->failed_login_attempts + 1;
        $user->update([
            'failed_login_attempts' => $percobaan,
            'lockout_until' => $percobaan >= 5 ? Carbon::now()->addMinutes(15) : null,
        ]);

        return back()->withErrors(['login' => 'Kata sandi yang Anda masukkan salah.'])->withInput();
    }

    // 4. Proses Daftar Akun Baru (Enkripsi Email & Phone + Simpan Email Hash)
    public function register(Request $request)
    {
       $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'email'    => 'required|email|max:255',
            'phone'    => ['required', 'regex:/^[0-9]+$/', 'min:10', 'max:15'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            // Pesan Error Kustom Bahasa Indonesia
            'phone.regex'        => 'Nomor telepon tidak valid! Mohon masukkan dengan angka saja.',
            'phone.min'          => 'Nomor telepon minimal harus 10 digit angka.',
            'phone.max'          => 'Nomor telepon maksimal 15 digit angka.',
            'password.min'       => 'Kata sandi minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.*'         => 'Kata sandi wajib mengandung kombinasi huruf besar, huruf kecil, angka, dan karakter spesial (seperti @, #, !, $).',
        ]);

        // Buat hash SHA-256 untuk mengecek apakah email sudah pernah terdaftar
        $emailHash = hash('sha256', strtolower(trim($request->email)));

        if (User::where('email_hash', $emailHash)->exists()) {
            return back()->withErrors(['email' => 'Alamat email ini sudah terdaftar.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'username'      => $request->username,
                'email'         => strtolower(trim($request->email)), // Otomatis dienkripsi oleh Model User
                'email_hash'    => $emailHash,                        // Disimpan dalam bentuk hash SHA-256
                'phone'         => $request->phone,                   // Otomatis dienkripsi oleh Model User
                'password_hash' => Hash::make($request->password),
                'status'        => 'ACTIVE',
            ]);

            $roleBuyer = Role::where('role_name', 'BUYER')->first();
            if ($roleBuyer) {
                DB::table('user_roles')->insert([
                    'user_id' => $user->id,
                    'role_id' => $roleBuyer->id,
                    'status'  => 'ACTIVE',
                ]);
            }

            DB::commit();
            Auth::login($user);

            return redirect()->route('home')->with('success', 'Pendaftaran berhasil! Anda terdaftar sebagai Pembeli (Buyer).');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['register' => 'Terjadi kesalahan saat mendaftar: ' . $e->getMessage()])->withInput();
        }
    }

    // 5. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
    // 6. Tampilkan Halaman Profil Pengguna
    public function profile()
    {
        $user = Auth::user();
        $roles = $user->roles()->pluck('role_name')->toArray();
        return view('profile', compact('user', 'roles'));
    }

    // 7. Proses Upgrade Role dari Buyer menjadi Seller
    public function upgradeToSeller()
    {
        $user = Auth::user();

        if ($user->hasRole('SELLER')) {
            return back()->with('success', 'Anda sudah memiliki akses sebagai Penjual (Seller).');
        }

        $roleSeller = Role::where('role_name', 'SELLER')->first();
        if ($roleSeller) {
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => $roleSeller->id,
                'status'  => 'ACTIVE',
            ]);
        }

        return back()->with('success', 'Selamat! Akun Anda berhasil di-upgrade menjadi Penjual (Seller).');
    }
}