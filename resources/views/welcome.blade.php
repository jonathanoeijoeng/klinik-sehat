<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Klinik Modern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/logo/favicon.png" sizes="any">
    {{-- <link rel="icon" href="/favicon.svg" type="image/svg+xml"> --}}
    <link rel="apple-touch-icon" href="/logo/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body {
            font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif, ;
        }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased">

    <nav class="sticky top-0 z-50 glass-morphism">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a class="flex items-center gap-2" href="/">
                    <img src="/logo/favicon.png" alt="Logo KlinikSehat"
                        class="h-8 w-auto
                    <span class="text-xl font-bold tracking-tight
                        text-green-900">SobatKlinik</span>
                </a>
                <div class="hidden md:flex space-x-8 text-sm font-medium text-slate-600">
                    <a href="#fitur" class="hover:text-green-600 transition">Fitur</a>
                    <a href="#harga" class="hover:text-green-600 transition">Harga</a>
                </div>
                <div class="flex items-center gap-4 text-sm not-has-[nav]:hidden">
                    @if (Route::has('login'))
                        <nav class="flex items-center justify-end gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}"
                                    class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <header class="relative bg-green-50 pt-8 pb-24 overflow-hidden border-b border-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">

                <div class="text-center md:text-left">
                    <span
                        class="inline-block px-4 py-1.5 mb-6 text-xs font-semibold tracking-widest text-green-600 uppercase bg-green-50 rounded-full">
                        Sistem Klinik Masa Depan
                    </span>
                    <h1
                        class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 mb-6 tracking-tight leading-tight">
                        Kelola Klinik Lebih <span class="text-green-600">Cerdas & Efisien.</span>
                    </h1>
                    <p class="max-w-2xl mx-auto md:mx-0 text-lg text-slate-600 mb-10 leading-relaxed">
                        Platform all-in-one untuk manajemen pasien, rekam medis elektronik (RME), dan farmasi yang telah
                        terstandarisasi SATUSEHAT Kemenkes RI.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center md:justify-start gap-4">
                        <button
                            class="px-8 py-4 bg-slate-900 text-white rounded-xl font-semibold hover:bg-slate-800 transition shadow-md">
                            Mulai Sekarang
                        </button>
                        <button
                            class="px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-xl font-semibold hover:bg-slate-50 transition">
                            Pelajari Integrasi
                        </button>
                    </div>
                </div>

                <div class="relative mt- md:mt-0">
                    <div class="absolute -top-10 -left-10 w-72 h-72 bg-green-50 rounded-full blur-3xl opacity-70"></div>
                    <div class="absolute -bottom-10 -right-10 w-72 h-72 bg-teal-50 rounded-full blur-3xl opacity-70">
                    </div>

                    <img src="/images/clinics.webp" alt="Mockup Antarmuka Sistem Informasi Klinik"
                        class="relative z-10 w-auto h-3/4 transform">
                </div>

            </div>
        </div>
    </header>

    <section id="fitur" class="py-60 bg-green-50 h-full border-b border-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Fitur Unggulan</h2>
                <p class="text-slate-500 mt-4">Didesain untuk mempercepat alur kerja tenaga medis.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div
                    class="p-8 rounded-3xl border border-slate-100 bg-white hover:border-green-200 transition group shadow-sm">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 mb-6 group-hover:bg-green-600 group-hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">RME Terintegrasi</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">Pencatatan SOAP elektronik yang rapi, aman, dan
                        mudah diakses dari perangkat manapun.</p>
                </div>

                <div id="integrasi"
                    class="p-8 rounded-3xl border border-green-100 bg-white relative overflow-hidden shadow-sm">
                    <div class="absolute top-0 right-0 p-4">
                        <span
                            class="text-[10px] font-bold bg-green-600 text-white px-2 py-1 rounded">TERVERIFIKASI</span>
                    </div>
                    <div class="w-12 h-12 bg-green-600 rounded-2xl flex items-center justify-center text-white mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">SATUSEHAT Integration</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">Sinkronisasi data otomatis ke platform SATUSEHAT
                        Kemenkes RI tanpa input ganda.</p>
                </div>

                <div
                    class="p-8 rounded-3xl border border-slate-100 bg-white hover:border-green-200 transition group shadow-sm">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600 mb-6 group-hover:bg-green-600 group-hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Antrean Real-time</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">Kelola antrean pasien dengan sistem estimasi
                        waktu
                        tunggu yang akurat dan transparan.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="harga" class="py-24 bg-green-50">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-12">Investasi Terbaik untuk Klinik</h2>
            <div class="bg-white p-10 rounded-3xl shadow-xl shadow-slate-200 border border-slate-100">
                <p class="text-slate-500 font-medium mb-2">Paket Profesional</p>
                <div class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-6">
                    IDR 300,000 <span class="text-lg text-slate-400 font-normal">/ bulan</span>
                </div>
                <ul class="text-left space-y-4 mb-10 text-slate-600 max-w-sm mx-auto">
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                        Integrasi API SATUSEHAT
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                        Unlimited Rekam Medis
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                        Modul Apotek
                    </li>
                </ul>
                <button
                    class="w-full py-4 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 shadow-lg shadow-green-100 transition">
                    Pilih Paket Ini
                </button>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-100 py-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-slate-500 text-sm">
            <p>&copy; 2026 SobatKlinik Indonesia. Semua hak dilindungi undang-undang.</p>
        </div>
    </footer>

</body>

</html>
