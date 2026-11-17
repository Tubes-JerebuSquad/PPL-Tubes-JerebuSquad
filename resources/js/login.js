/**
 * RimbaWatch - Login Page Script
 * Menangani konfigurasi tema Tailwind CSS dan interaktivitas form login.
 */

// 1. Konfigurasi Tema Tailwind CSS
window.tailwind = window.tailwind || {};
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
      },
      colors: {
        rimba: {
          dark: '#143423',
          primary: '#1F4D36',
          leaf: '#2D6B4B',
          sage: '#7FA489',
          mint: '#E8F2EC',
          cream: '#FAF9F5',
          surface: '#F4F2EA',
          charcoal: '#1A241F',
          muted: '#627068',
          border: '#E3DFD3'
        }
      },
      boxShadow: {
        'card': '0 20px 45px -10px rgba(20, 52, 35, 0.08), 0 4px 12px -2px rgba(20, 52, 35, 0.04)',
      }
    }
  }
};

// 2. Toggle visibilitas kata sandi (Password Eye Icon)
function togglePassword() {
  const input = document.getElementById('userPassword');
  const icon = document.getElementById('pwdEyeIcon');
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
    `;
  } else {
    input.type = 'password';
    icon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    `;
  }
}

// Daftarkan ke window agar dapat dipanggil dari onclick
window.togglePassword = togglePassword;
