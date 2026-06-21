# UNSCollab 

**UNSCollab** adalah platform kolaborasi mahasiswa berbasis web yang dirancang khusus untuk memfasilitasi pencarian tim proyek, manajemen magang, serta validasi dokumen pendukung secara terintegrasi. Proyek ini dibangun menggunakan **Laravel 11**, **Vite**, dan memanfaatkan **Supabase (PostgreSQL & Storage)** sebagai backend database serta penyimpanan awan, serta dideploy secara serverless di **Vercel**.

---

## 🛠️ Fitur Utama

- **Autentikasi & Manajemen Pengguna**: Registrasi dan login terpisah untuk Mahasiswa, Perusahaan/Mitra, dan Admin.
- **Dashboard Dinamis**: Halaman kustom untuk memantau status magang, tim aktif, dan data statistik platform.
- **Daftar & Validasi Magang**: Fitur bagi mahasiswa untuk mendaftar lowongan magang beserta unggah dokumen pendukung (PDF/Gambar).
- **Manajemen Tim (Daftar Team)**: Membantu mahasiswa mencari atau membentuk tim kolaborasi proyek intra-kampus.
- **Supabase Storage Integration**: Penyimpanan untuk dokumen pendukung magang langsung ke cloud storage.
- **Validasi Admin**: Halaman khusus admin untuk menyetujui, menolak, atau memvalidasi berkas pendaftaran magang.

---

## 💻 Arsitektur & Teknologi

- **Framework**: Laravel 11 (PHP 8.3+)
- **Frontend Asset Bundler**: Vite
- **Database**: PostgreSQL (Hosted on Supabase)
- **Deployment Platform**: Vercel (Serverless PHP Environment)

---

## 🚀 Panduan Instalasi Lokal (Langkah-langkah)

Jika ingin menjalankan atau mengembangkan proyek ini di komputer lokal, ikuti petunjuk ini:

### 1. Klon Repositori
```bash
git clone [https://github.com/aszteroid/UNSCollab-Web.git](https://github.com/aszteroid/UNSCollab-Web.git)
cd UNSCollab-Web
