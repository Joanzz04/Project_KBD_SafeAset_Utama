<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

    // 3. Proses Login (Untuk Pengguna Umum & Agent)
    public function login(Request $request)
    {
        $request->validate([
            'login'      => 'required|string',
            'password'   => 'required|string',
            'login_type' => 'required|in:user,agent', // Membedakan form biasa vs form agent
        ]);

        $user = User::where('email', $request->login)
                    ->orWhere('username', $request->login)
                    ->first();

        if (!$user) {
            return back()->withErrors(['login' => 'Akun tidak ditemukan.'])->withInput();
        }

        // Cek fitur Lockout Brute-Force
        if ($user->lockout_until && Carbon::now()->isBefore($user->lockout_until)) {
            $menit = Carbon::now()->diffInMinutes($user->lockout_until) + 1;
            return back()->withErrors(['login' => "Akun terkunci sementara. Coba lagi dalam {$menit} menit."]);
        }

        if ($user->status === 'SUSPENDED') {
            return back()->withErrors(['login' => 'Akun Anda sedang ditangguhkan.']);
        }

        // Cek kecocokan password
        if (Hash::check($request->password, $user->password_hash)) {
            
            // Validasi Khusus: Jika login dari tab "Login sebagai Agent"
            if ($request->login_type === 'agent' && !$user->hasRole('AGENT')) {
                return back()->withErrors(['login' => 'Akses ditolak! Akun Anda bukan akun Agent resmi.']);
            }

            // Validasi Khusus: Jika akun Agent mencoba login di form pengguna biasa
            if ($request->login_type === 'user' && $user->hasRole('AGENT')) {
                return back()->withErrors(['login' => 'Akun Agent wajib masuk melalui tab "Login sebagai Agent".']);
            }

            // Reset gagal login
            $user->update(['failed_login_attempts' => 0, 'lockout_until' => null]);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Selamat datang kembali, ' . $user->username . '!');
        }

        // Jika password salah
        $percobaan = $user->failed_login_attempts + 1;
        $user->update([
            'failed_login_attempts' => $percobaan,
            'lockout_until' => $percobaan >= 5 ? Carbon::now()->addMinutes(15) : null,
        ]);

        return back()->withErrors(['login' => 'Kata sandi yang Anda masukkan salah.'])->withInput();
    }

// 4. Proses Daftar Akun Baru (Otomatis role BUYER)
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => 'required|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username'      => $request->username,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'password_hash' => Hash::make($request->password),
                'status'        => 'ACTIVE',
            ]);

            // Setiap user baru yang mendaftar otomatis mendapatkan role BUYER (ID: 1)
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
}