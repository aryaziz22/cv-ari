# Email Setup Guide - CV-Ari Portfolio

Panduan untuk setup email functionality di portfolio.

## 📧 Email Configuration

### File Structure
```
assets/php/
├── send-email.example.php    ✅ Public (di GitHub)
├── SmtpMailer.example.php    ✅ Public (di GitHub)
├── send-email.php            ❌ Private (tidak di GitHub)
└── SmtpMailer.php            ❌ Private (tidak di GitHub)
```

### Setup Steps

#### 1. Persiapan Gmail Account
- Buka https://myaccount.google.com/apppasswords
- Pilih **App: Mail** dan **Device: Windows/Mac/Linux**
- Google akan generate **16-character App Password**
- Copy password ini (contoh: `rnkb etfz jzyg hvit`)

#### 2. Buat File send-email.php
```bash
# Di folder: assets/php/
cp send-email.example.php send-email.php
```

#### 3. Edit send-email.php
Buka file dan ubah constants dibawah:
```php
define('GMAIL_USERNAME', 'your-email@gmail.com');          // Email Gmail kamu
define('GMAIL_PASSWORD', 'your-app-password-16-char');    // App Password dari step 1
define('GMAIL_FROM_EMAIL', 'your-email@gmail.com');       // Sama seperti username
define('GMAIL_FROM_NAME', 'Your Name Portfolio');         // Nama yang muncul di email
$to = 'your-receiving-email@gmail.com';                   // Email tujuan (dimana email masuk)
```

#### 4. Buat File SmtpMailer.php
```bash
# Di folder: assets/php/
cp SmtpMailer.example.php SmtpMailer.php
```

#### 5. Test Email
- Jalankan server PHP: `php -S localhost:8000`
- Buka `http://localhost:8000/index.html`
- Isi form contact dan klik **Send Message**
- Cek email yang dituju

### 🔒 Security Notes

- ✅ **send-email.example.php** dan **SmtpMailer.example.php** di-upload ke GitHub (template)
- ❌ **send-email.php** dan **SmtpMailer.php** TIDAK di-upload (ada credentials)
- File .php di-exclude melalui `.gitignore`
- App Password lebih aman daripada password real (bisa di-revoke kapan saja)

### 📁 .gitignore

File `.gitignore` sudah di-setup untuk exclude:
- `assets/php/*.php` (kecuali .example.php)
- `.env` files
- `.vscode/` dan IDE files
- Log files dan cache
- Node modules
- OS files (.DS_Store, Thumbs.db)

### 🚀 Deployment

Ketika deploy ke hosting (Heroku, Vercel, Netlify, dll):
1. Buat file `send-email.php` di hosting server
2. Set environment variables atau isikan credentials langsung
3. **Jangan** upload credentials ke GitHub - gunakan environment variables di hosting panel

### ❓ Troubleshooting

**Email tidak terkirim:**
- Cek App Password sudah benar (16 karakter)
- Pastikan Gmail account tidak memiliki 2FA yang belum disetup
- Coba remove dan buat App Password baru

**Error "Failed to connect to mailserver":**
- Pastikan email kamu menggunakan Gmail (bukan email custom domain)
- Periksa bahwa port 587 tidak di-block oleh ISP/firewall

**Form validation error:**
- Pastikan semua field (name, email, subject, message) terisi
- Email harus format valid (ada @)

---

**Created**: May 2026  
**Updated**: Setup documentation for email functionality with Gmail SMTP
