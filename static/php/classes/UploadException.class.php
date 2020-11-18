<?php

/**
 * Returns a human readable error description for file upload errors.
 *
 * @author Dan Brown <danbrown@php.net>
 * @author Michiel Thalen
 * @copyright Copyright © 1997 - 2016 by the PHP Documentation Group
 * @license
 * UploadException is licensed under a Creative Commons Attribution 3.0 License
 * or later.
 *
 * Based on a work at
 * https://secure.php.net/manual/en/features.file-upload.errors.php#89374.
 *
 * You should have received a copy of the Creative Commons Attribution 3.0
 * License with this program. If not, see
 * <https://creativecommons.org/licenses/by/3.0/>.
 */


class UploadException extends Exception
{
    public function __construct($code)
    {
        $message = $this->codeToMessage($code);
        parent::__construct($message, 500);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was '.
                           'specified in the HTML form';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'The uploaded file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Missing a temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Failed to write file to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = 'File upload stopped by extension';
                break;

            default:
                $message = 'Unknown upload error';
                break;
        }

        return $message;
    }
}
