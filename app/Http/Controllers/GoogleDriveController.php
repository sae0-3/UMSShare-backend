<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoogleDriveController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $googleToken = $user->google_token;

        if (!$googleToken) {
            return response()->json(['error' => 'Token de Google no encontrado'], 401);
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $fileName = $file->getClientOriginalName();

        try {
            $googleDriveFile = $this->googleDriveService->uploadFile(
                $filePath,
                $fileName,
                $user->google_drive_folder_id,
                $user
            );

            return response()->json([
                'message' => 'Archivo subido exitosamente!',
                'google_drive_file_id' => $googleDriveFile->id,
                'file_name' => $googleDriveFile->name,
                'mime_type' => $googleDriveFile->mimeType,
                'file_url' => $googleDriveFile->webViewLink,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al subir el archivo a Google Drive', 'message' => $e->getMessage()], 500);
        }
    }


    public function uploadMultipleFiles(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,png,pdf,docx',
        ]);

        $uploadedFiles = [];

        $user = auth()->user();

        foreach ($request->file('files') as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getClientOriginalName();

            $uploadedFiles[] = $this->googleDriveService->uploadFile(
                $filePath,
                $fileName,
                $user->google_drive_folder_id,
                $user
            );
        }

        return response()->json([
            'status' => 'success',
            'files' => $uploadedFiles,
        ]);
    }
}
