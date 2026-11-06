<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeAsset - Masuk atau Daftar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #1e293b; }
        .bg-navy-dark { background-color: #0f172a; }
        .bg-gold { background-color: #9a6b4c; }
        .bg-gold:hover { background-color: #82583d; }
    </style>
</head>
<body class="bg-navy min-h-screen flex flex-col justify-between text-gray-800">

    <!-- Header Sederhana -->
    <div class="p-6">
        <a href="{{ route('home') }}" class="text-white flex items-center space-x-2 w-max hover:text-gray-300">
            <i class="fa-solid fa-arrow-left"></i>
            <span class="text-sm font-medium">Kembali ke Beranda SafeAsset</span>
        </a>
    </div>

    <!-- Kotak Utama Autentikasi -->
    <div class="max-w-md w-full mx-auto bg-white rounded-xl shadow-2xl overflow-hidden my-auto">
        
        <!-- Header Card -->
        <div class="bg-navy-dark text-white p-6 text-center">
            <div class="w-12 h-12 border-2 border-[#b58360] rounded flex items-center justify-center text-[#b58360] mx-auto mb-2">
                <i class="fa-solid fa-shield-halved text-2xl"></i>
            </div>
            <h2 class="text-xl font-bold">SafeAsset Portal</h2>
            <p class="text-xs text-gray-400 mt-1">Pilih metode akses akun Anda di bawah ini</p>
        </div>

        <!-- Navigasi 3 Tab: Login | Daftar | Login Agent -->
        <div class="grid grid-cols-3 border-b border-gray-200 bg-gray-100 text-xs font-semibold text-gray-600">
            <button onclick="switchTab('login')" id="tab-login" class="py-3.5 text-center border-b-2 border-[#9a6b4c] text-[#9a6b4c] bg-white">
                <i class="fa-solid fa-right-to-bracket mr-1"></i> Login
            </button>
            <button onclick="switchTab('register')" id="tab-register" class="py-3.5 text-center hover:bg-gray-50">
                <i class="fa-solid fa-user-plus mr-1"></i> Daftar
            </button>
            <button onclick="switchTab('agent')" id="tab-agent" class="py-3.5 text-center hover:bg-gray-50">
                <i class="fa-solid fa-user-tie mr-1"></i> Login Agent
            </button>
        </div>

        <div class="p-6">
            <!-- Pesan Error Validasi -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-xs mb-4">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- 1. FORM LOGIN PENGGUNA (BUYER / SELLER) -->
            <form id="form-login" method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="login_type" value="user">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Email atau Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#9a6b4c]" placeholder="Masukkan email / username">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kata Sandi</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#9a6b4c]" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-gold text-white font-semibold py-2.5 rounded text-sm transition">
                    Masuk ke Akun
                </button>
            </form>

            <!-- 2. FORM DAFTAR AKUN BARU (OTOMATIS BUYER) -->
            <form id="form-register" method="POST" action="{{ route('register.submit') }}" class="space-y-3 hidden">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Contoh: budi_santoso">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="nama@email.com">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="08123456789">
                </div>

                <!-- Keterangan Otomatis Role Buyer -->
                <div class="bg-gray-50 border border-gray-200 p-2.5 rounded text-xs text-gray-600">
                    <i class="fa-solid fa-circle-info text-[#9a6b4c] mr-1"></i>
                    Akun baru akan terdaftar sebagai <b>Pembeli (Buyer)</b>. Anda dapat mengajukan upgrade menjadi Penjual (Seller) nanti di halaman profil.
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kata Sandi</label>
                        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Ulangi Sandi</label>
                        <input type="password" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" placeholder="Ulangi sandi">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gold text-white font-semibold py-2.5 rounded text-sm transition mt-2">
                    Daftar Sebagai Pembeli
                </button>
            </form>

            <!-- 3. FORM LOGIN KHUSUS AGENT (TANPA OPSI DAFTAR) -->
            <form id="form-agent" method="POST" action="{{ route('login.submit') }}" class="space-y-4 hidden">
                @csrf
                <input type="hidden" name="login_type" value="agent">

                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-3 rounded text-xs">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    <b>Portal Khusus Agent:</b> Akun Agent ditunjuk dan dibuat langsung oleh Administrator SafeAsset. Tidak tersedia pendaftaran mandiri untuk Agent.
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">ID / Email Agent Resmi</label>
                    <input type="text" name="login" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-600" placeholder="Masukkan email / username Agent">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kata Sandi Agent</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-600" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-navy-dark hover:bg-slate-800 text-white font-semibold py-2.5 rounded text-sm transition">
                    Masuk sebagai Agent
                </button>
            </form>

        </div>
    </div>

    <div class="text-center text-gray-500 text-xs py-4">
        &copy; 2026 SafeAsset Protocol. All rights reserved.
    </div>

    <!-- Script Pengaturan Tab -->
    <script>
        function switchTab(tab) {
            const forms = ['login', 'register', 'agent'];
            forms.forEach(f => {
                document.getElementById('form-' + f).classList.add('hidden');
                document.getElementById('tab-' + f).classList.remove('border-b-2', 'border-[#9a6b4c]', 'text-[#9a6b4c]', 'bg-white');
            });

            document.getElementById('form-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).classList.add('border-b-2', 'border-[#9a6b4c]', 'text-[#9a6b4c]', 'bg-white');
        }
    </script>
</body>
</html>
