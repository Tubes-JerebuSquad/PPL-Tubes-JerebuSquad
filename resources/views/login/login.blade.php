<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RimbaWatch - Masuk Akun Warga</title>
  
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Custom Login JS (Tailwind Config & Interaktivitas) -->
  <script src="{{ asset('js/login.js') }}"></script>
  
  <!-- Custom Login CSS -->
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-rimba-mint selection:text-rimba-dark relative">

  <!-- Hiasan latar belakang bernuansa alam lembut -->
  <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-rimba-mint/70 rounded-full blur-3xl opacity-70"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-[#E1EEE5]/60 rounded-full blur-3xl opacity-60"></div>
    <!-- Pola kontur/topografi halus -->
    <svg class="absolute inset-0 w-full h-full opacity-[0.035]" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="topo" width="60" height="60" patternUnits="userSpaceOnUse">
          <circle cx="30" cy="30" r="18" fill="none" stroke="#1F4D36" stroke-width="1.2"/>
          <circle cx="30" cy="30" r="28" fill="none" stroke="#1F4D36" stroke-width="0.8"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#topo)" />
    </svg>
  </div>

  <!-- Top Simple Navigation -->
  <header class="relative z-10 w-full max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-xl bg-rimba-primary flex items-center justify-center text-white shadow-sm ring-2 ring-rimba-mint">
        <!-- Sprout Icon -->
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 20h10"/>
          <path d="M10 20c5.5-2.5.8-6.4 3-10"/>
          <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
          <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.4 1.7-4.6-2.7.2-4.1 1.1-4.9 2z"/>
        </svg>
      </div>
      <span class="text-lg font-extrabold tracking-tight text-rimba-dark">Rimba<span class="text-rimba-leaf">Watch</span></span>
    </a>

    <!-- Opsi Tamu Cepat di Header -->
    <a 
      href="{{ url('/lapor') }}" 
      class="text-xs font-semibold text-rimba-primary hover:text-rimba-leaf bg-white/80 hover:bg-white border border-rimba-border px-3.5 py-2 rounded-xl transition-all shadow-xs flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-rimba-leaf" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <span>Lapor Cepat (Mode Tamu)</span>
    </a>
  </header>

  <!-- Container Konten Tengah -->
  <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-4 sm:py-8">
    <div class="w-full max-w-[430px] fade-up">

      <!-- Kartu Login -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-card border border-rimba-border transition-all duration-300">
        
        <!-- Header Sambutan Warga -->
        <div class="text-center space-y-1.5 mb-6">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rimba-mint/60 text-rimba-primary text-[11px] font-semibold border border-rimba-sage/30 mb-1">
            <span class="w-1.5 h-1.5 rounded-full bg-rimba-leaf"></span>
            Portal Warga & Komunitas
          </div>
          <h1 class="text-2xl font-bold text-rimba-dark tracking-tight">Masuk Akun Rimba</h1>
          <p class="text-xs text-rimba-muted leading-relaxed max-w-xs mx-auto">
            Pantau laporan aduan lingkungan dan dukung kegiatan restorasi hutan di daerahmu.
          </p>
        </div>

        <!-- Tombol Google -->
        <a 
          href="{{ url('/auth/google') }}" 
          class="w-full py-2.5 px-4 rounded-xl border border-rimba-border hover:border-rimba-sage bg-white hover:bg-rimba-surface/50 text-xs font-semibold text-rimba-charcoal flex items-center justify-center gap-2.5 transition-all duration-150 shadow-xs active:scale-[0.99]">
          <svg class="w-4 h-4" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
          </svg>
          <span>Masuk Cepat dengan Google</span>
        </a>

        <!-- Garis Pemisah Sederhana -->
        <div class="relative my-5">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-rimba-border"></div>
          </div>
          <div class="relative flex justify-center text-[11px]">
            <span class="px-2.5 bg-white text-rimba-muted font-medium">atau masuk dengan akun</span>
          </div>
        </div>

        <!-- Notifikasi Status & Error Laravel -->
        @if (session('status'))
          <div class="mb-4 rounded-xl border border-rimba-leaf/20 bg-rimba-mint/60 p-3 text-xs text-rimba-dark">
            {{ session('status') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-600">
            <ul class="list-disc list-inside space-y-0.5">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <!-- Formulir Input -->
        <form method="POST" action="{{ url('/login') }}" class="space-y-4">
          @csrf
          
          <!-- Input WhatsApp / Email -->
          <div class="space-y-1.5">
            <label for="userIdentifier" class="block text-xs font-semibold text-rimba-charcoal">
              No. WhatsApp atau Email
            </label>
            <div class="relative rounded-xl border border-rimba-border bg-[#FCFCFA] focus-within:bg-white focus-within:border-rimba-leaf focus-within:ring-2 focus-within:ring-rimba-mint transition-all">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-rimba-muted">
                <!-- User / Phone icon -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <input 
                type="text" 
                id="userIdentifier" 
                name="email"
                required
                autocomplete="username"
                placeholder="0812xxxxxxx atau email@anda.com" 
                value="{{ old('email') }}"
                class="w-full pl-10 pr-3.5 py-2.5 bg-transparent text-xs sm:text-sm text-rimba-dark rounded-xl focus:outline-none placeholder-gray-400 font-medium"
              />
            </div>
          </div>

          <!-- Input Kata Sandi -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label for="userPassword" class="block text-xs font-semibold text-rimba-charcoal">
                Kata Sandi
              </label>
              <a href="{{ Route::has('password.request') ? route('password.request') : url('/forgot-password') }}" class="text-[11px] font-medium text-rimba-leaf hover:underline">
                Lupa sandi?
              </a>
            </div>
            <div class="relative rounded-xl border border-rimba-border bg-[#FCFCFA] focus-within:bg-white focus-within:border-rimba-leaf focus-within:ring-2 focus-within:ring-rimba-mint transition-all">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-rimba-muted">
                <!-- Lock Icon -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/>
                  <path d="M7 11V7a5 5 0 0110 0v4" stroke-width="2"/>
                </svg>
              </div>
              <input 
                type="password" 
                id="userPassword" 
                name="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi" 
                class="w-full pl-10 pr-10 py-2.5 bg-transparent text-xs sm:text-sm text-rimba-dark rounded-xl focus:outline-none placeholder-gray-400 font-medium"
              />
              <button 
                type="button" 
                onclick="togglePassword()" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-rimba-muted hover:text-rimba-dark transition-colors"
                aria-label="Tampilkan atau sembunyikan kata sandi">
                <svg id="pwdEyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Opsi Ingat Saya -->
          <div class="flex items-center justify-between text-xs pt-0.5">
            <label class="flex items-center gap-2 cursor-pointer select-none text-rimba-muted">
              <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-3.5 h-3.5 rounded text-rimba-primary focus:ring-rimba-mint border-rimba-border">
              <span>Ingat saya</span>
            </label>
            <span class="text-[11px] text-rimba-leaf font-medium flex items-center gap-1">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              Enkripsi Aman
            </span>
          </div>

          <!-- Tombol Masuk Utama -->
          <button 
            type="submit" 
            class="w-full mt-2 bg-rimba-primary hover:bg-rimba-dark active:scale-[0.99] text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition-all duration-150 flex items-center justify-center gap-2 text-xs sm:text-sm">
            <span>Masuk ke RimbaWatch</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
          </button>
        </form>

        <!-- Bagian Bawah: Opsi Daftar & Mode Tamu -->
        <div class="mt-6 pt-5 border-t border-rimba-border text-center space-y-3">
          <p class="text-xs text-rimba-muted">
            Belum punya akun warga? 
            <a href="{{ Route::has('register') ? route('register') : url('/register') }}" class="font-bold text-rimba-primary hover:underline">
              Daftar sekarang
            </a>
          </p>

          <div class="pt-1">
            <a 
              href="{{ url('/lapor') }}" 
              class="w-full text-xs text-rimba-leaf hover:text-rimba-dark font-medium py-2 px-3 rounded-lg bg-rimba-mint/40 hover:bg-rimba-mint transition-colors border border-rimba-sage/20 flex items-center justify-center gap-1.5">
              <span>Mau lapor temuan di hutan tanpa akun?</span>
              <strong class="font-semibold underline">Mode Tamu &rarr;</strong>
            </a>
          </div>
        </div>

      </div>

      <!-- Footer Hak Cipta Ringkas -->
      <footer class="mt-6 text-center text-[11px] text-rimba-muted">
        <p>&copy; 2026 RimbaWatch. Platform Kolaboratif Penjaga Kawasan Hijau.</p>
      </footer>
    </div>
  </main>

</body>
</html>
