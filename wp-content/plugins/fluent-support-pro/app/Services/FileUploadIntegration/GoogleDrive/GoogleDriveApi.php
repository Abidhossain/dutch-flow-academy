<?php

namespace FluentSupportPro\App\Services\FileUploadIntegration\GoogleDrive;


use FluentSupport\Framework\Support\Arr;

class GoogleDriveApi
{
    private $api = 'https://www.googleapis.com/';

    public function uploadToDrive($file, $ticketId = 0)
    {
        $accessToken = GoogleDriveHelper::getAccessToken();

        if (is_wp_error($accessToken)) {
            return $accessToken;
        }

        $fileName = 'ticket_' . $ticketId . '__' . $file->id . '__' . $file->title;
        $mimeType = $file->file_type;
        $fileContent = file_get_contents($file->file_path);

        $url = $this->api . 'upload/drive/v3/files?uploadType=multipart';

        $boundary = wp_generate_password(24);
        $headers = array(
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => "multipart/related; boundary={$boundary}",
        );

        $folderId = $this->getParentFolderId($ticketId);

        $body = "--$boundary\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode(array(
                'name'    => $fileName,
                'parents' => array($folderId)
            )) . "\r\n";

        $body .= "--$boundary\r\n" .
            "Content-Type: $mimeType\r\n" .
            "Content-Transfer-Encoding: base64\r\n\r\n" .
            base64_encode($fileContent) .
            "\r\n--$boundary--\r\n";

        $headers['Content-Length'] = strlen($body);

        $args = array(
            'headers' => $headers,
            'body'    => $body,
            'method'  => 'POST',
            'timeout' => 30
        );

        $response = wp_remote_post($url, $args);
        $responseBody = $this->verifyAndFetchResponse($response, 'id');

        if (is_wp_error($responseBody)) {
            return $responseBody;
        }

        $link = $this->genarateShareLink($responseBody['id']);

        if (is_wp_error($link)) {
            return $link;
        }

        return [
            'full_url' => $link,
            'file_id'  => $responseBody['id'],
        ];
    }

    // Generate share link
    private function genarateShareLink($fileId)
    {
        $access_token = GoogleDriveHelper::getAccessToken();

        // Set the permission
        $url = 'https://www.googleapis.com/drive/v3/files/' . $fileId . '/permissions';

        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json'
            ),
            'body'    => json_encode(array(
                'role' => 'reader',
                'type' => 'anyone'
            ))
        );
        $response = wp_remote_post($url, $args);

        $response = $this->verifyAndFetchResponse($response, 'id');
        if (is_wp_error($response)) {
            return $response;
        }

        // If the permission is set successfully, retrieve the shareable link
        $url = 'https://www.googleapis.com/drive/v3/files/' . $fileId . '?fields=webViewLink';
        $headers = array(
            'Authorization' => 'Bearer ' . $access_token
        );
        $args = array(
            'headers' => $headers
        );
        $response = wp_remote_get($url, $args);
        $response = $this->verifyAndFetchResponse($response, 'webViewLink');
        if (is_wp_error($response)) {
            return $response;
        }

        return $response['webViewLink'];
    }

    public function delete($attachment)
    {

    }

    public function verifyAndFetchResponse($response, $verifyKey = '')
    {
        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code > 299) {
            $error = wp_remote_retrieve_response_message($response);
            $errorText = sprintf(__('Unable to upload file in Google Drive %s', 'fluent-support-pro'), $error);
            return new \WP_Error('gdrive_upload_failed', $errorText);
        }

        $responseBody = wp_remote_retrieve_body($response);
        $responseBody = json_decode($responseBody, true);

        if ($verifyKey) {
            if (!isset($responseBody[$verifyKey])) {
                $errorText = __('Unable to upload file in google drive', 'fluent-support-pro');
                return new \WP_Error('gdrive_upload_failed', $errorText);
            }
        }

        return $responseBody;
    }

    public function getParentFolderId($ticketId = null)
    {
        $googleDrive = GoogleDriveHelper::getSettings();
        $mainFolderId = Arr::get($googleDrive, 'folder_id');

        // Check if the main folder exists; if not, create it
        if (!$mainFolderId) {
            $mainFolderId = $this->createMainFolder();
            if (is_wp_error($mainFolderId)) {
                return $mainFolderId;
            }

            // Save the main folder ID in settings
            $settings = GoogleDriveHelper::getSettings();
            $settings['folder_id'] = $mainFolderId;
            GoogleDriveHelper::updateSettings($settings);
        }

        // Check if a ticket ID is provided
        if ($ticketId) {
            return $this->getTicketFolder($mainFolderId, $ticketId);
        }

        return $mainFolderId;
    }

    private function createMainFolder()
    {
        $url = 'https://www.googleapis.com/drive/v3/files';
        $mainFolderName = 'fs-files-for-' . str_replace(['https://', 'http://', '/'], '', site_url());

        $args = array(
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . GoogleDriveHelper::getAccessToken(),
                'Content-Type'  => 'application/json'
            ],
            'body'    => json_encode(array(
                'name'     => $mainFolderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ))
        );

        $response = wp_remote_post($url, $args);
        $response = $this->verifyAndFetchResponse($response, 'id');

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['id'];
    }

    private function getTicketFolder($mainFolderId, $ticketId)
    {
        $subFolderName = 'ticket_' . $ticketId;

        // Check if ticket folder exists
        $existingFolder = $this->findFolder($subFolderName, $mainFolderId);
        if ($existingFolder) {

            return $existingFolder['id'];
        }

        // If ticket folder does not exist, create it
        return $this->createTicketFolder($mainFolderId, $ticketId);
    }

    private function createTicketFolder($mainFolderId, $ticketId)
    {
        $url = 'https://www.googleapis.com/drive/v3/files';
        $subFolderName = 'ticket_' . $ticketId;

        $args = array(
            'method'  => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . GoogleDriveHelper::getAccessToken(),
                'Content-Type'  => 'application/json'
            ],
            'body'    => json_encode(array(
                'name'     => $subFolderName,
                'parents'  => array($mainFolderId),
                'mimeType' => 'application/vnd.google-apps.folder'
            ))
        );

        $response = wp_remote_post($url, $args);
        $response = $this->verifyAndFetchResponse($response, 'id');

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['id'];
    }

    private function findFolder($folderName, $parentFolderId)
    {
        $url = 'https://www.googleapis.com/drive/v3/files';

        $queryParams = array(
            'q' => sprintf("name='%s' and '%s' in parents  and mimeType='application/vnd.google-apps.folder' and trashed=false", 	                $folderName, $parentFolderId),
            'fields' => 'files(id, name)'
        );

        // Build the URL with query parameters
        $url = add_query_arg($queryParams, $url);

        $args = array(
            'method'  => 'GET',
            'headers' => [
                'Authorization' => 'Bearer ' . GoogleDriveHelper::getAccessToken(),
                'Content-Type'  => 'application/json'
            ]
        );

        $response = wp_remote_get($url, $args);

        $response = $this->verifyAndFetchResponse($response, 'files');

        if (is_wp_error($response)) {
            return false;
        }

        return !empty($response['files']) ? $response['files'][0] : false;
    }

}
