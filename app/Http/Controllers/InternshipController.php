<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SupabaseStorageService;

class InternshipController extends Controller
{
    private SupabaseStorageService $storage;

    public function __construct(SupabaseStorageService $storage)
    {
        $this->storage = $storage;
    }

    public function store(Request $request)
    {
        $companyId = session('type_id');

        if (!$companyId) {
            return response()->json(['success' => false, 'message' => 'Sesi Anda telah habis atau Anda tidak memiliki akses.'], 401);
        }

        $title         = trim($request->input('title', ''));
        $description   = trim($request->input('description', ''));
        $requirements  = trim($request->input('requirements', ''));
        $benefit       = trim($request->input('benefit', ''));
        $location      = trim($request->input('location', ''));
        $workMode      = trim($request->input('work_mode', 'onsite'));
        $paymentStatus = trim($request->input('payment_status', 'unpaid'));
        $quota         = intval($request->input('quota', 1));
        $duration      = trim($request->input('duration', ''));
        $deadline      = $request->input('deadline', null);

        if (empty($title) || empty($description) || empty($requirements)) {
            return response()->json(['success' => false, 'message' => 'Judul, deskripsi, dan kualifikasi wajib diisi'], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file  = $request->file('image');
            $error = $this->storage->validateFile($file, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
            if (!$error) {
                $result = $this->storage->upload($file, 'banner', $companyId);
                if ($result['success']) {
                    $imagePath = $result['path'];
                }
            }
        }

        $docPath = null;
        if ($request->hasFile('supporting_document')) {
            $file  = $request->file('supporting_document');
            $error = $this->storage->validateFile($file, ['pdf', 'doc', 'docx'], 10 * 1024 * 1024);
            if (!$error) {
                $result = $this->storage->upload($file, 'dokumen-pendukung', $companyId);
                if ($result['success']) {
                    $docPath = $result['path'];
                }
            }
        }

        DB::table('internships')->insert([
            'id_internship'       => DB::raw('gen_random_uuid()'),
            'id_company'          => $companyId,
            'title'               => $title,
            'description'         => $description,
            'requirement'         => $requirements,
            'benefit'             => $benefit,
            'location'            => $location,
            'work_mode'           => $workMode,
            'payment_status'      => $paymentStatus,
            'quota'               => $quota,
            'duration'            => $duration,
            'deadline'            => $deadline,
            'image'               => $imagePath,
            'supporting_document' => $docPath,
            'approval_status'     => 'pending',
            'posted_at'           => now(),
        ]);

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Membuat lowongan: ' . $title,
            'created_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dikirim! Menunggu review admin.',
        ]);
    }

    public function update(Request $request)
    {
        $companyId    = session('type_id');
        $idInternship = trim($request->input('id_internship', ''));

        if (!$companyId || empty($idInternship)) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }

        $internship = DB::table('internships')
            ->where('id_internship', $idInternship)
            ->where('id_company', $companyId)
            ->first();

        if (!$internship) {
            return response()->json(['success' => false, 'message' => 'Lowongan tidak ditemukan'], 404);
        }

        if ($internship->approval_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Lowongan hanya bisa diedit selama masih pending'], 403);
        }

        $title        = trim($request->input('title', ''));
        $description  = trim($request->input('description', ''));
        $requirements = trim($request->input('requirements', ''));

        if (empty($title) || empty($description) || empty($requirements)) {
            return response()->json(['success' => false, 'message' => 'Judul, deskripsi, dan kualifikasi wajib diisi'], 400);
        }

        $updateData = [
            'title'          => $title,
            'description'    => $description,
            'requirement'    => $requirements,
            'benefit'        => trim($request->input('benefit', '')),
            'location'       => trim($request->input('location', '')),
            'work_mode'      => trim($request->input('work_mode', 'onsite')),
            'payment_status' => trim($request->input('payment_status', 'unpaid')),
            'quota'          => intval($request->input('quota', 1)),
            'duration'       => trim($request->input('duration', '')),
            'deadline'       => $request->input('deadline', null),
        ];

        if ($request->hasFile('image')) {
            $file  = $request->file('image');
            $error = $this->storage->validateFile($file, ['jpg', 'jpeg', 'png', 'webp'], 2 * 1024 * 1024);
            if (!$error) {
                $result = $this->storage->upload($file, 'banner', $companyId);
                if ($result['success']) {
                    if (!empty($internship->image)) {
                        $this->storage->delete('banner', $internship->image);
                    }
                    $updateData['image'] = $result['path'];
                }
            }
        }

        if ($request->hasFile('supporting_document')) {
            $file  = $request->file('supporting_document');
            $error = $this->storage->validateFile($file, ['pdf', 'doc', 'docx'], 10 * 1024 * 1024);
            if (!$error) {
                $result = $this->storage->upload($file, 'dokumen-pendukung', $companyId);
                if ($result['success']) {
                    if (!empty($internship->supporting_document)) {
                        $this->storage->delete('dokumen-pendukung', $internship->supporting_document);
                    }
                    $updateData['supporting_document'] = $result['path'];
                }
            }
        }

        DB::table('internships')->where('id_internship', $idInternship)->update($updateData);

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Mengedit lowongan: ' . $title,
            'created_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lowongan berhasil diperbarui!']);
    }

    public function destroy(Request $request)
    {
        $companyId    = session('type_id');
        $idInternship = trim($request->input('id_internship', ''));

        if (!$companyId || empty($idInternship)) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid'], 400);
        }

        $internship = DB::table('internships')
            ->where('id_internship', $idInternship)
            ->where('id_company', $companyId)
            ->first();

        if (!$internship) {
            return response()->json(['success' => false, 'message' => 'Lowongan tidak ditemukan'], 404);
        }

        if ($internship->approval_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Lowongan hanya bisa dihapus selama masih pending'], 403);
        }

        // Hapus file terkait dari Supabase Storage sebelum hapus record DB
        if (!empty($internship->image)) {
            $this->storage->delete('banner', $internship->image);
        }
        if (!empty($internship->supporting_document)) {
            $this->storage->delete('dokumen-pendukung', $internship->supporting_document);
        }

        DB::table('internships')->where('id_internship', $idInternship)->delete();

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Menghapus lowongan: ' . $internship->title,
            'created_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lowongan berhasil dihapus']);
    }

    // FIX #2: Nama method disamakan dengan web.php yang memanggil 'updateApplicantStatus'
    public function updateApplicantStatus(Request $request)
    {
        $idStudent    = trim($request->input('id_student', ''));
        $idInternship = trim($request->input('id_internship', ''));
        $status       = trim($request->input('status', ''));

        if (empty($idStudent) || empty($idInternship)) {
            return response()->json(['success' => false, 'message' => 'ID Student atau ID Internship tidak valid'], 400);
        }

        if (!in_array($status, ['pending', 'reviewed', 'accepted', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid'], 400);
        }

        $updated = DB::table('applications')
            ->where('id_student', $idStudent)
            ->where('id_internship', $idInternship)
            ->update(['application_status' => $status]);

        if (!$updated) {
            return response()->json(['success' => false, 'message' => 'Data pelamar tidak ditemukan atau tidak ada perubahan status'], 404);
        }

        $companyId   = session('type_id');
        $statusLabel = ['pending' => 'Menunggu', 'reviewed' => 'Direview', 'accepted' => 'Diterima', 'rejected' => 'Ditolak'][$status] ?? $status;

        DB::table('activity_logs')->insert([
            'id_activity_log' => DB::raw('gen_random_uuid()'),
            'id_company'      => $companyId,
            'action'          => 'Update status pelamar menjadi: ' . $statusLabel,
            'created_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status pelamar berhasil diperbarui']);
    }
}