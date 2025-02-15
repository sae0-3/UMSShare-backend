<?php

namespace App\Services;

use Google\Service\Drive\DriveFile;
use Google_Client;
use Google_Service_Drive;

class GoogleDriveService
{
    protected $client, $service;

    public function __construct()
    {
        // $user = auth()->user();

        // if (!$user) {
        //     throw new \Exception('User is not authenticated. AAAAAAAAAAAAAA!');
        // }

        // $this->client = new Google_Client();
        // $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        // $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

        // if (!$user->google_token) {
        //     throw new \Exception('Google token not available for user.');
        // }

        // $this->client->setAccessToken($user->google_token);
        // $this->client->addScope(Google_Service_Drive::DRIVE);

        // $this->service = new Google_Service_Drive($this->client);
    }

    // public function createFolder($folderName)
    // {
    //     $this->validateAccessToken();

    //     $service = new Google_Service_Drive($this->googleClient);
    //     $folderId = $this->getExistingFolderId($service, 'UMSShare');

    //     if (!$folderId) {
    //         $folderMedatadata = new DriveFile([
    //             'name' => $folderName,
    //             'mimeType' => 'application/vnd.google-apps.folder',
    //         ]);

    //         $folder = $service->files->create($folderMedatadata, [
    //             'fields' => 'id',
    //         ]);

    //         $folderId = $folder->id;
    //     }

    //     return $folderId;
    // }

    // public function getExistingFolderId(Google_Service_Drive $service, $folderName)
    // {
    //     try {
    //         $query = "mimeType = 'application/vnd.google-apps.folder' and name = '" . addslashes($folderName) . "'";
    //         $results = $service->files->listFiles([
    //             'q' => $query,
    //             'fields' => 'files(id, name)',
    //         ]);

    //         return count($results->files) > 0 ? $results->files[0]->id : null;
    //     } catch (\Exception $e) {
    //         \Log::error('Failed to check existing folder: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    // public function validateAccessToken()
    // {
    //     if ($this->googleClient->isAccessTokenExpired()) {
    //         if ($this->googleClient->getRefreshToken()) {
    //             $this->googleClient->fetchAccessTokenWithRefreshToken($this->googleClient->getRefreshToken());
    //         } else {
    //             throw new \Exception('Access token expired and no refresh token available.');
    //         }
    //     }
    // }

    public function uploadFile($filePath, $fileName, $folderId, $user)
    {
        $accessToken = $user->google_token;

        $this->client = new Google_Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setAccessToken($accessToken);
        $this->client->addScope(Google_Service_Drive::DRIVE);

        $this->service = new Google_Service_Drive($this->client);

        $file = new DriveFile();
        $file->setName($fileName);
        $file->setParents([$folderId]);

        $content = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath);

        $fileMetadata = $this->service->files->create(
            $file,
            [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]
        );

        return $fileMetadata;
    }
}
