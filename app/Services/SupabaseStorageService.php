<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    private string $url;
    private string $key;

    public function __construct()
    {
        $this->url = rtrim(config('services.supabase.url'), '/');
        $this->key = config('services.supabase.key');
    }

    /**
     * Upload file ke bucket Supabase tertentu.
     *
     * @param UploadedFile $file       File yang diupload user
     * @param string       $bucket     Nama bucket: 'logos' | 'documents' | 'cv' | 'banners'
     * @param string       $folder     Sub-folder di dalam bucket (opsional), misal 'company-1'
     * @return array{success: bool, path: ?string, public_url: ?string, error: ?string}
     */
    public function upload(UploadedFile $file, string $bucket, string $folder = ''): array
    {
        $ext      = strtolower($file->getClientOriginalExtension());
        $filename = uniqid() . '_' . time() . '.' . $ext;
        $path     = $folder ? trim($folder, '/') . '/' . $filename : $filename;

        $fileContent = file_get_contents($file->getRealPath());
        $endpoint    = "{$this->url}/storage/v1/object/{$bucket}/{$path}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
            'apikey'        => $this->key,
            'Content-Type'  => $file->getClientMimeType(),
        ])->withBody($fileContent, $file->getClientMimeType())->put($endpoint);

        if (!$response->successful()) {
            return [
                'success'    => false,
                'path'       => null,
                'public_url' => null,
                'error'      => $response->body(),
            ];
        }

        return [
            'success'    => true,
            'path'       => $path,
            'public_url' => $this->publicUrl($bucket, $path),
            'error'      => null,
        ];
    }

    /**
     * Hapus file dari bucket Supabase.
     *
     * @param string $bucket
     * @param string $path   Path file relatif di dalam bucket (yang disimpan saat upload)
     */
    public function delete(string $bucket, string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        $endpoint = "{$this->url}/storage/v1/object/{$bucket}/{$path}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
            'apikey'        => $this->key,
        ])->delete($endpoint);

        return $response->successful();
    }

    /**
     * Bentuk public URL dari bucket + path.
     * Asumsi bucket sudah di-set Public di Supabase Dashboard.
     */
    public function publicUrl(string $bucket, string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    /**
     * Validasi ekstensi & ukuran file sebelum upload.
     *
     * @param UploadedFile $file
     * @param array        $allowedExt   contoh: ['jpg','jpeg','png','webp']
     * @param int          $maxSizeBytes contoh: 2 * 1024 * 1024 (2MB)
     */
    public function validateFile(UploadedFile $file, array $allowedExt, int $maxSizeBytes): ?string
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, $allowedExt)) {
            return 'Format file harus: ' . implode(', ', $allowedExt);
        }

        if ($file->getSize() > $maxSizeBytes) {
            $maxMb = round($maxSizeBytes / (1024 * 1024), 1);
            return "Ukuran file maksimal {$maxMb}MB";
        }

        return null; // valid
    }
}