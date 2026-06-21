<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    // Public endpoint
    public function index()
    {
        $certificates = Certificate::orderBy('date', 'desc')
            ->get()
            ->map(function ($cert) {
                return $this->transformCertificate($cert);
            });

        return response()->json([
            'data' => $certificates,
        ]);
    }

    // Admin endpoints
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'issuer'         => 'required|string|max:255',
            'description'    => 'nullable|string',
            'date'           => 'nullable|date',
            'expires_date'   => 'nullable|date',
            'credential_url' => 'nullable|url|max:2048',
            'image'          => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:2048',
        ]);

        // Handle image/PDF upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('certificates', 'public');
        }

        $certificate = Certificate::create($validated);

        return response()->json([
            'message' => 'Certificate created successfully',
            'data'    => $this->transformCertificate($certificate),
        ], 201);
    }

    public function show(Certificate $certificate)
    {
        return response()->json([
            'data' => $this->transformCertificate($certificate),
        ]);
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'issuer'         => 'required|string|max:255',
            'description'    => 'nullable|string',
            'date'           => 'nullable|date',
            'expires_date'   => 'nullable|date',
            'credential_url' => 'nullable|url|max:2048',
            'image'          => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:2048',
        ]);

        // Handle image/PDF upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($certificate->image) {
                Storage::disk('public')->delete($certificate->image);
            }
            $validated['image'] = $request->file('image')->store('certificates', 'public');
        }

        $certificate->update($validated);

        return response()->json([
            'message' => 'Certificate updated successfully',
            'data'    => $this->transformCertificate($certificate),
        ]);
    }

    public function destroy(Certificate $certificate)
    {
        // Delete image file
        if ($certificate->image) {
            Storage::disk('public')->delete($certificate->image);
        }

        $certificate->delete();

        return response()->json([
            'message' => 'Certificate deleted successfully',
        ]);
    }

    /**
     * Transform certificate model to include full image URL.
     */
    private function transformCertificate(Certificate $cert): array
    {
        $data = $cert->toArray();
        if ($cert->image) {
            $data['image'] = str_starts_with($cert->image, 'http')
                ? $cert->image
                : asset('storage/' . $cert->image);
        }
        return $data;
    }

    /**
     * Get PDF thumbnail (first page as image)
     * URL: /api/certificates/{id}/thumbnail
     */
    public function getPdfThumbnail(Certificate $certificate)
    {
        if (!$certificate->image || !str_ends_with(strtolower($certificate->image), '.pdf')) {
            return response()->json(['error' => 'Not a PDF'], 400);
        }

        try {
            $pdfPath = storage_path('app/public/' . $certificate->image);
            
            if (!file_exists($pdfPath)) {
                return response()->json(['error' => 'PDF not found'], 404);
            }

            // Create thumbnail filename
            $thumbFilename = 'cert_thumb_' . $certificate->id . '.jpg';
            $thumbPath = storage_path('app/public/certificate-thumbnails');
            $thumbFullPath = $thumbPath . '/' . $thumbFilename;

            // Return cached thumbnail if exists
            if (file_exists($thumbFullPath)) {
                return response()->json([
                    'thumbnail' => asset('storage/certificate-thumbnails/' . $thumbFilename),
                ]);
            }

            // Create thumbnails directory if needed
            if (!is_dir($thumbPath)) {
                mkdir($thumbPath, 0755, true);
            }

            // Try ImageMagick first
            $command = sprintf(
                'convert -density 150 %s[0] -quality 85 %s 2>&1',
                escapeshellarg($pdfPath),
                escapeshellarg($thumbFullPath)
            );

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // If ImageMagick failed, try Ghostscript
            if ($returnVar !== 0) {
                $gsCommand = sprintf(
                    'gs -q -dNOPAUSE -dBATCH -dSAFER -sDEVICE=jpeg -dTextAlphaBits=4 -r150 -sOutputFile=%s %s 2>&1',
                    escapeshellarg($thumbFullPath),
                    escapeshellarg($pdfPath)
                );

                $output = [];
                exec($gsCommand, $output, $returnVar);
            }

            // If both failed, return error with details
            if ($returnVar !== 0 || !file_exists($thumbFullPath)) {
                \Log::error('PDF thumbnail conversion failed', [
                    'cert_id' => $certificate->id,
                    'pdf_path' => $pdfPath,
                    'output' => $output,
                    'return_code' => $returnVar
                ]);

                return response()->json([
                    'error' => 'Could not convert PDF to image',
                    'details' => 'Server does not have ImageMagick or Ghostscript installed',
                    'output' => implode(' ', $output ?? [])
                ], 500);
            }

            return response()->json([
                'thumbnail' => asset('storage/certificate-thumbnails/' . $thumbFilename),
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF thumbnail error', [
                'cert_id' => $certificate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Conversion failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
