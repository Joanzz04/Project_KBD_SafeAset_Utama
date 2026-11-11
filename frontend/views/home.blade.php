<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeAsset - Protect Your Future</title>
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
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- NAVBAR -->
    <nav class="bg-[#0f172a] text-white px-8 py-4 flex items-center justify-between border-b border-gray-700">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center space-x-3">
            <img src="{{ asset('assets/images/logo_web.png') }}" alt="SafeAsset Logo" class="h-12 w-auto object-contain">
            <div>
                <span class="text-xl font-bold tracking-wider text-white">Safe<span class="font-light text-gray-300">Asset</span></span>
                <p class="text-[10px] text-gray-400 -mt-1">Your Trusted Property Partner</p>
            </div>
        </a>

        <!-- Menu Tengah -->
        <div class="hidden md:flex space-x-8 text-sm font-medium text-gray-300">
            <a href="{{ route('home') }}" class="text-white border-b-2 border-[#b58360] pb-1">Home</a>
            <a href="#listings" class="hover:text-white transition">Properties</a>
            <a href="#why" class="hover:text-white transition">Invest Securely</a>
            <a href="#" class="hover:text-white transition">About Us</a>
            <a href="#" class="hover:text-white transition">Contact</a>
        </div>

        <!-- Menu Kanan (Profil / Login & List Property) -->
        <div class="flex items-center space-x-4">
            @auth
                <span class="text-sm text-gray-300">Halo, <b>{{ auth()->user()->username }}</b></span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded transition">Logout</button>
                </form>
            @else
                <!-- Tombol Ikon User mengarah ke halaman Login/Daftar -->
                <a href="{{ route('auth.form') }}" class="w-9 h-9 rounded-full bg-gray-700 hover:bg-gray-600 flex items-center justify-center text-gray-200 transition" title="Login / Daftar">
                    <i class="fa-regular fa-user text-sm"></i>
                </a>
            @endauth

            <a href="{{ auth()->check() ? '#' : route('auth.form') }}" class="bg-[#e2dacb] text-gray-900 font-semibold text-sm px-4 py-2 rounded hover:bg-[#d4c8b4] transition">
                List Your Property
            </a>
        </div>
    </nav>

    <!-- NOTIFIKASI BERHASIL LOGIN/DAFTAR -->
    @if(session('success'))
        <div class="bg-green-600 text-white text-center py-2 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- HERO SECTION -->
    <section class="bg-navy text-white pt-16 pb-20 px-4 text-center">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-4">
                Protect Your Future.<br>Invest in Secure Properties.
            </h1>
            <p class="text-gray-300 text-base md:text-lg mb-10 max-w-2xl mx-auto">
                Browse curated, vetted real estate opportunities with advanced security and transparent ownership.
            </p>

            <!-- Search Bar -->
            <div class="bg-white rounded-lg p-2 flex flex-col md:flex-row items-center shadow-lg max-w-3xl mx-auto text-gray-700">
                <div class="flex items-center px-4 py-2 w-full md:w-1/3 border-b md:border-b-0 md:border-r border-gray-200">
                    <i class="fa-solid fa-magnifying-glass text-gray-400 mr-3"></i>
                    <select class="w-full bg-transparent focus:outline-none text-sm">
                        <option>Property Type</option>
                        <option>Rumah Tapak</option>
                        <option>Apartemen</option>
                        <option>Tanah Kavling</option>
                    </select>
                </div>
                <div class="flex items-center px-4 py-2 w-full md:w-1/3">
                    <i class="fa-solid fa-location-dot text-gray-400 mr-3"></i>
                    <input type="text" placeholder="Location (City / District)" class="w-full focus:outline-none text-sm">
                </div>
                <div class="w-full md:w-1/3 p-1">
                    <button class="w-full bg-gold text-white font-semibold py-2.5 px-6 rounded text-sm transition">
                        Find Safe Properties
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED SECURE LISTINGS (KOSONG KARENA BELUM ADA PROPERTI) -->
    <section id="listings" class="max-w-6xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Featured Secure Listings</h2>
            <span class="text-xs text-gray-500 flex items-center mt-2 md:mt-0">
                <i class="fa-solid fa-shield-check text-gold mr-1"></i> Secured by Safe Asset Protocol
            </span>
        </div>

        @if($properties->isEmpty())
            <!-- Tampilan Kosong (Empty State) -->
            <div class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-12 text-center my-6">
                <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-house-lock text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-1">Belum Ada Properti yang Didaftarkan</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                    Saat ini belum ada listing properti terverifikasi yang tersedia. Jadilah penjual pertama yang mendaftarkan properti aman di SafeAsset.
                </p>
                <a href="{{ auth()->check() ? '#' : route('auth.form') }}" class="inline-block bg-gold text-white text-sm font-medium px-6 py-2.5 rounded transition">
                    + Daftarkan Properti Pertama
                </a>
            </div>
        @else
            <!-- Jika nanti ada properti di database, otomatis tampil di sini -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($properties as $prop)
                    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                        <div class="p-4">
                            <h3 class="font-bold text-lg">{{ $prop->title }}</h3>
                            <p class="text-gold font-semibold">Rp {{ number_format($prop->price, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $prop->district }}, {{ $prop->city }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- WHY SAFE ASSET SECTION -->
    <section id="why" class="max-w-6xl mx-auto px-6 py-10 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 items-start">
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Why Safe Asset?</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Platform jual-beli properti dengan sistem rekening bersama (Escrow) dan verifikasi sertifikat berlapis.
                </p>
            </div>
            <div class="flex space-x-3">
                <div class="text-gold text-2xl mt-1"><i class="fa-solid fa-user-shield"></i></div>
                <div>
                    <h4 class="font-bold text-sm text-gray-800">Aman & Terlindungi</h4>
                    <p class="text-xs text-gray-500 mt-1">Dana ditahan di rekening Escrow hingga verifikasi dokumen selesai.</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <div class="text-gray-700 text-2xl mt-1"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <h4 class="font-bold text-sm text-gray-800">Verified Vetted</h4>
                    <p class="text-xs text-gray-500 mt-1">Setiap sertifikat properti dan identitas penjual diverifikasi secara ketat.</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <div class="text-gold text-2xl mt-1"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div>
                    <h4 class="font-bold text-sm text-gray-800">Easy Investment</h4>
                    <p class="text-xs text-gray-500 mt-1">Proses negosiasi harga transparan didampingi oleh agen profesional.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-navy-dark text-gray-400 py-12 px-8 mt-12 text-xs">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div>
                <h5 class="text-white font-bold mb-3">Company Info</h5>
                <p class="leading-relaxed">SafeAsset adalah platform transaksi properti aman dengan perlindungan Escrow dan audit berlapis.</p>
            </div>
            <div>
                <h5 class="text-white font-bold mb-3">Company</h5>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-white">Home</a></li>
                    <li><a href="#" class="hover:text-white">Tentang Kami</a></li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-bold mb-3">Link</h5>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-white">Properties</a></li>
                    <li><a href="#" class="hover:text-white">Invest Securely</a></li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-bold mb-3">Help</h5>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-white">FAQ</a></li>
                    <li><a href="#" class="hover:text-white">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-6xl mx-auto border-t border-gray-800 pt-6 flex justify-between items-center">
            <span>Created by Kelompok SafeAset</span>
            <div class="space-x-4">
                <i class="fa-brands fa-twitter hover:text-white cursor-pointer"></i>
                <i class="fa-brands fa-facebook hover:text-white cursor-pointer"></i>
                <i class="fa-brands fa-instagram hover:text-white cursor-pointer"></i>
            </div>
        </div>
    </footer>

</body>
</html>