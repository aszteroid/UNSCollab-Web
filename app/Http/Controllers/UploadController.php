<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UploadController extends Controller
{
    public function uploadToSupabase(Request $request)
    {
        $request->validate([
            'foto_produk' => 'required|image|max:2048',
        ]);

        $file      = $request->file('foto_produk');
        $namaFile  = time() . '_' . $file->getClientOriginalName();
        $fileKonten = file_get_contents($file->getRealPath());

        // FIX #5: Ambil credentials dari .env, bukan hardcode di sini
        // Tambahkan ke file .env kamu:
        //   SUPABASE_URL=https://qdcjgonjjrxhghlbdarz.supabase.co
        //   SUPABASE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
        //   SUPABASE_BUCKET=logo-comp
        $supabaseUrl = config('services.supabase.url');
        $supabaseKey = config('services.supabase.key');
        $namaBucket  = config('services.supabase.bucket');

        $urlTujuan = "{$supabaseUrl}/storage/v1/object/{$namaBucket}/uploads/{$namaFile}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $supabaseKey,
            'API-KEY'       => $supabaseKey,
            'Content-Type'  => $file->getClientMimeType(),
        ])->withBody($fileKonten, $file->getClientMimeType())->put($urlTujuan);

        if ($response->successful()) {
            return back()->with('success', 'File berhasil diupload ke Supabase.');
        } else {
            return back()->with('error', 'Gagal upload: ' . $response->body());
        }
    }
}