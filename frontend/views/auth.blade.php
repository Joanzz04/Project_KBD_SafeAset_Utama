<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeAsset - Masuk atau Daftar</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_web.png') }}?v=2">
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
        <div class="bg-[#0f172a] text-white p-6 text-center">
            <img src="{{ asset('assets/images/logo_web.png') }}" alt="SafeAsset Logo" class="h-16 w-auto mx-auto mb-3 object-contain">
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

                <!-- Input Alamat Gmail dengan Validasi @gmail.com -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat Gmail</label>
                    <input type="email" id="reg-email" name="email" value="{{ old('email') }}" oninput="validateRegisterForm()" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#9a6b4c]" placeholder="nama@gmail.com">
                    
                    <!-- Pesan Peringatan Merah (Muncul jika tidak berakhiran @gmail.com) -->
                    <p id="email-error" class="text-[11px] text-red-600 font-medium mt-1 hidden">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Wajib menggunakan alamat Gmail yang valid (berakhiran @gmail.com)!
                    </p>
                </div>

                <!-- Input Nomor Telepon / WhatsApp dengan Validasi Angka Real-time -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" id="reg-phone" name="phone" value="{{ old('phone') }}" oninput="validatePhoneInput(this)" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#9a6b4c]" placeholder="08123456789">
                    
                    <!-- Pesan Peringatan Merah (Muncul otomatis jika ada huruf/simbol) -->
                    <p id="phone-error" class="text-[11px] text-red-600 font-medium mt-1 hidden">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Mohon masukkan dengan angka saja!
                    </p>
                </div>

                <!-- Keterangan Otomatis Role Buyer -->
                <div class="bg-gray-50 border border-gray-200 p-2.5 rounded text-xs text-gray-600">
                    <i class="fa-solid fa-circle-info text-[#9a6b4c] mr-1"></i>
                    Akun baru akan terdaftar sebagai <b>Pembeli (Buyer)</b>. Anda dapat mengajukan upgrade menjadi Penjual (Seller) nanti di halaman profil.
                </div>

                <!-- Input Kata Sandi & Ulangi Sandi  -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kata Sandi</label>
                        <div class="relative">
                            <input type="password" id="reg-password" name="password" oninput="checkPasswordStrength(this.value)" onkeyup="checkPasswordStrength(this.value)" required class="w-full pl-3 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#9a6b4c]" placeholder="Min. 8 karakter">
                            <button type="button" onclick="togglePassword('reg-password', 'eye-reg-pass')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" title="Lihat/Sembunyikan Sandi">
                                <i id="eye-reg-pass" class="fa-regular fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Ulangi Sandi</label>
                        <div class="relative">
                            <input type="password" id="reg-password-confirm" name="password_confirmation" required class="w-full pl-3 pr-9 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-[#9a6b4c]" placeholder="Ulangi sandi">
                            <button type="button" onclick="togglePassword('reg-password-confirm', 'eye-reg-confirm')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" title="Lihat/Sembunyikan Sandi">
                                <i id="eye-reg-confirm" class="fa-regular fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Indikator Password Policy Real-time -->
                <div class="bg-gray-50 p-3 rounded border border-gray-200 text-[11px] space-y-1.5 text-gray-500">
                    <p class="font-semibold text-gray-700 mb-1">Syarat Keamanan Kata Sandi:</p>
                    <div id="rule-length" class="flex items-center transition-colors duration-200">
                        <i class="fa-solid fa-circle-xmark text-gray-400 mr-1.5 w-3.5"></i> Minimal 8 karakter
                    </div>
                    <div id="rule-case" class="flex items-center transition-colors duration-200">
                        <i class="fa-solid fa-circle-xmark text-gray-400 mr-1.5 w-3.5"></i> Huruf besar (A-Z) & huruf kecil (a-z)
                    </div>
                    <div id="rule-number" class="flex items-center transition-colors duration-200">
                        <i class="fa-solid fa-circle-xmark text-gray-400 mr-1.5 w-3.5"></i> Minimal 1 angka (0-9)
                    </div>
                    <div id="rule-symbol" class="flex items-center transition-colors duration-200">
                        <i class="fa-solid fa-circle-xmark text-gray-400 mr-1.5 w-3.5"></i> Karakter spesial (!@#$%^&* dll)
                    </div>
                </div>

                <!-- Script Langsung untuk Cek Kekuatan Password -->
                <script>
                    function checkPasswordStrength(val) {
                        const rules = [
                            { id: 'rule-length', valid: val.length >= 8 },
                            { id: 'rule-case',   valid: /[a-z]/.test(val) && /[A-Z]/.test(val) },
                            { id: 'rule-number', valid: /[0-9]/.test(val) },
                            { id: 'rule-symbol', valid: /[^A-Za-z0-9]/.test(val) }
                        ];

                        rules.forEach(rule => {
                            const el = document.getElementById(rule.id);
                            if (!el) return;
                            const icon = el.querySelector('i');
                            if (rule.valid) {
                                el.className = 'flex items-center text-green-600 font-semibold transition-colors duration-200';
                                icon.className = 'fa-solid fa-circle-check text-green-600 mr-1.5 w-3.5';
                            } else {
                                el.className = 'flex items-center text-gray-500 transition-colors duration-200';
                                icon.className = 'fa-solid fa-circle-xmark text-gray-400 mr-1.5 w-3.5';
                            }
                        });
                    }
                function validateRegisterForm() {
                const emailInput = document.getElementById('reg-email');
                const emailError = document.getElementById('email-error');
                const phoneInput = document.getElementById('reg-phone');
                const phoneError = document.getElementById('phone-error');
                const submitBtn  = document.getElementById('btn-register');

                let isEmailValid = true;
                let isPhoneValid = true;

                // 1. Validasi Gmail (Wajib format email valid dan berakhiran @gmail.com)
                const emailVal = emailInput.value.trim();
                const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/i;

                if (emailVal.length > 0 && !gmailRegex.test(emailVal)) {
                    isEmailValid = false;
                    emailError.classList.remove('hidden');
                    emailInput.classList.add('border-red-500', 'bg-red-50');
                    emailInput.classList.remove('border-gray-300');
                } else {
                    emailError.classList.add('hidden');
                    emailInput.classList.remove('border-red-500', 'bg-red-50');
                    emailInput.classList.add('border-gray-300');
                }

                // 2. Validasi Nomor Telepon (Hanya angka & minimal 11 digit)
                const phoneVal = phoneInput.value;
                const hasNonNumber = /[^0-9]/.test(phoneVal);
                const isTooShort = phoneVal.length > 0 && phoneVal.length < 11;

                if (hasNonNumber) {
                    isPhoneValid = false;
                    phoneError.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i> Mohon masukkan dengan angka saja!';
                    phoneError.classList.remove('hidden');
                    phoneInput.classList.add('border-red-500', 'bg-red-50');
                    phoneInput.classList.remove('border-gray-300');
                } else if (isTooShort) {
                    isPhoneValid = false;
                    phoneError.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i> Nomor telepon minimal harus 11 digit angka!';
                    phoneError.classList.remove('hidden');
                    phoneInput.classList.add('border-red-500', 'bg-red-50');
                    phoneInput.classList.remove('border-gray-300');
                } else {
                    phoneError.classList.add('hidden');
                    phoneInput.classList.remove('border-red-500', 'bg-red-50');
                    phoneInput.classList.add('border-gray-300');
                }

                // 3. Kunci tombol Daftar jika salah satu tidak valid
                if (!isEmailValid || !isPhoneValid) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
                function togglePassword(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
                }
                </script>

                <button type="submit" id="btn-register" class="w-full bg-gold text-white font-semibold py-2.5 rounded text-sm transition mt-2">
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
