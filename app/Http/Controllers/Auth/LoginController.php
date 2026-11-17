<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Menampilkan formulir login warga / pengguna.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var Pengguna $user */
            $user = Auth::user();

            return redirect($this->getDashboardRouteForUser($user));
        }

        return view('login.login');
    }

    /**
     * Alias method untuk menampilkan formulir login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        return $this->create();
    }

    /**
     * Memproses permintaan autentikasi login pengguna.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Nomor WhatsApp atau Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited($request);

        $identifier = trim($request->input('email'));
        $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'telepon';

        // Cek apakah akun terdaftar dan apakah statusnya aktif
        $pengguna = Pengguna::where($fieldType, $identifier)->first();

        if ($pengguna && ! $pengguna->isAktif()) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        $credentials = [
            $fieldType => $identifier,
            'password' => $request->input('password'),
            'status_akun' => Pengguna::STATUS_AKTIF,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => 'Username atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        /** @var Pengguna $user */
        $user = Auth::user();

        return redirect()->intended($this->getDashboardRouteForUser($user));
    }

    /**
     * Menentukan URL dashboard tujuan sesuai dengan peran pengguna.
     */
    protected function getDashboardRouteForUser(Pengguna $user): string
    {
        $intended = session()->get('url.intended');
        if ($intended) {
            $path = parse_url($intended, PHP_URL_PATH) ?? '';
            if (in_array(rtrim($path, '/'), ['', '/login'])) {
                session()->forget('url.intended');
            }
        }

        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($user->isPengawas()) {
            return route('pengawas.dashboard');
        }

        if ($user->isPetugasLapangan()) {
            return route('petugas.dashboard');
        }

        return route('masyarakat.dashboard');
    }

    /**
     * Alias method untuk memproses login.
     */
    public function login(Request $request): RedirectResponse
    {
        return $this->store($request);
    }

    /**
     * Mengeluarkan pengguna dari sesi (logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }

    /**
     * Alias method untuk logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        return $this->destroy($request);
    }

    /**
     * Memastikan percobaan login tidak melebihi batas (rate limiting).
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
        ]);
    }

    /**
     * Membuat kunci identifikasi throttle untuk rate limiter.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }
}
