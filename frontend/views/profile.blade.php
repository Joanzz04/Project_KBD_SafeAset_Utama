<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeAsset - Profil Pengguna</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_web.png') }}?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #1e293b; }
        .bg-navy-dark { background-color: #0f172a; }
        .bg-gold { background-color: #9a6b4c; }
        .bg-gold:hover { background-color: #82583d; }
        .text-gold { color: #b58360; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    <!-- NAVBAR -->
    <nav class="bg-navy-dark text-white px-8 py-4 flex items-center justify-between border-b border-gray-700">
        <a href="{{ route('home') }}" class="flex items-center space-x-3">
            <img src="{{ asset('assets/images/logo_web.png') }}" alt="SafeAsset Logo" class="h-12 w-auto object-contain">
            <div>
                <span class="text-xl font-bold tracking-wider text-white">Safe<span class="font-light text-gray-300">Asset</span></span>
                <p class="text-[10px] text-gray-400 -mt-1">Your Trusted Property Partner</p>
            </div>
        </a>

        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" class="text-sm text-gray-300 hover:text-white transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Beranda
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded transition">Logout</button>
            </form>
        </div>
    </nav>

    <!-- NOTIFIKASI OTOMATIS HILANG 3 DETIK -->
    @if(session('success'))
        <div id="flash-alert" class="bg-green-600 text-white text-center py-2.5 text-sm font-medium transition-opacity duration-500 ease-out">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                const alertBox = document.getElementById('flash-alert');
                if (alertBox) {
                    alertBox.style.opacity = '0';
                    setTimeout(() => alertBox.remove(), 500);
                }
            }, 3000);
        </script>
    @endif

    <!-- KONTEN UTAMA PROFIL -->
    <main class="max-w-4xl w-full mx-auto px-6 py-12 my-auto">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            
            <!-- Header Banner Profil -->
            <div class="bg-navy text-white p-6 flex items-center space-x-4">
                <div class="w-16 h-16 rounded-full bg-[#9a6b4c] flex items-center justify-center text-2xl font-bold uppercase">
                    {{ substr($user->username, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $user->username }}</h1>
                    <div class="flex items-center space-x-2 mt-1">
                        @foreach($roles as $role)
                            <span class="text-xs bg-[#e2dacb] text-gray-900 font-bold px-2.5 py-0.5 rounded">
                                {{ $role }}
                            </span>
                        @endforeach
                        <span class="text-xs bg-green-500/20 text-green-300 border border-green-500/30 px-2.5 py-0.5 rounded">
                            Status: {{ $user->status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Informasi Pengguna & Upgrade Role -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Kolom Kiri: Data Diri (Otomatis Didekripsi) -->
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Informasi Akun</h2>
                    
                    <div>
                        <label class="text-xs text-gray-400 uppercase font-semibold">Username</label>
                        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $user->username }}</p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-400 uppercase font-semibold">Alamat Email</label>
                        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $user->email }}</p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-400 uppercase font-semibold">Nomor Telepon / WhatsApp</label>
                        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $user->phone }}</p>
                    </div>
                </div>

                <!-- Kolom Kanan: Status Peran & Upgrade ke Penjual (Seller) -->
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Upgrade Akun Anda</h2>

                    @if(in_array('SELLER', $roles))
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center space-x-2 text-green-700 font-bold text-sm mb-1">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Akun Penjual (Seller) Aktif</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed mb-4">
                                Anda telah memiliki hak akses sebagai Pembeli sekaligus Penjual. Anda kini dapat mendaftarkan dan menjual properti di SafeAsset.
                            </p>
                            <a href="#" class="inline-block bg-gold text-white text-xs font-semibold px-4 py-2 rounded transition">
                                + Upload / Jual Properti Baru
                            </a>
                        </div>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-center space-x-2 text-amber-800 font-bold text-sm mb-1">
                                <i class="fa-solid fa-store"></i>
                                <span>Ingin Menjual Properti?</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed mb-4">
                                Saat ini Anda terdaftar sebagai <b>Pembeli (Buyer)</b>. Aktifkan fitur <b>Penjual (Seller)</b> pada akun Anda untuk mulai mengunggah listing properti.
                            </p>
                            <form action="{{ route('profile.upgrade') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-gold text-white font-semibold py-2.5 px-4 rounded text-xs transition shadow">
                                    Upgrade Role Menjadi Penjual (Seller)
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </main>

    <!-- FOOTER RINGKAS -->
    <footer class="bg-navy-dark text-gray-400 text-center py-4 text-xs">
        &copy; 2026 SafeAsset Protocol. All rights reserved.
    </footer>

</body>
</html>