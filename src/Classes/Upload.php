<?php

/**
 * Uguu
 *
 * @copyright Copyright (c) 2022 Go Johansson (nokonoko) <neku@pomf.se>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Pomf\Uguu\Classes;

use Exception;

class Upload extends Response
{
    public array $FILE_INFO;
    public array $fingerPrintInfo;
    private mixed $Connector;


    /**
     * @throws Exception
     */
    public function reFiles($files): array
    {
        $this->Connector = new Connector();
        $this->Connector->setDB($this->Connector->DB);
        $result = [];
        $files = $this->diverseArray($files);
        foreach ($files as $file) {
            $hash = sha1_file($file['tmp_name']);
            $this->FILE_INFO = [
                'TEMP_NAME' => $file['tmp_name'],
                'NAME' => strip_tags($file['name']),
                'SIZE' => $file['size'],
                'SHA1' => $hash,
                'EXTENSION' => $this->fileExtension($file),
                'MIME' => $this->fileMIME($file),
                'NEW_NAME' => $this->generateName($this->fileExtension($file), $hash)
            ];
            $result[] = [
                $this->FILE_INFO['TEMP_NAME'],
                $this->FILE_INFO['NAME'],
                $this->FILE_INFO['SIZE'],
                $this->FILE_INFO['SHA1'],
                $this->FILE_INFO['EXTENSION'],
                $this->FILE_INFO['MIME']
            ];
        }
        return $result;
    }
    public function diverseArray($files): array
    {
        $result = [];
        foreach ($files as $key1 => $value1) {
            foreach ($value1 as $key2 => $value2) {
                $result[$key2][$key1] = $value2;
            }
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public function uploadFile(): array
    {

        if ($this->Connector->CONFIG['RATE_LIMIT']) {
            $this->Connector->checkRateLimit($this->fingerPrintInfo);
        }

        if ($this->Connector->CONFIG['BLACKLIST_DB']) {
            $this->Connector->checkFileBlacklist($this->FILE_INFO);
        }

        if ($this->Connector->CONFIG['FILTER_MODE'] && empty($this->FILE_INFO['EXTENSION'])) {
            $this->checkMimeBlacklist();
        }

        if ($this->Connector->CONFIG['FILTER_MODE'] && !empty($this->FILE_INFO['EXTENSION'])) {
            $this->checkMimeBlacklist();
            $this->checkExtensionBlacklist();
        }

        if (!is_dir($this->Connector->CONFIG['FILES_ROOT'])) {
            throw new Exception('File storage path not accessible.', 500);
        }

        if (
            !move_uploaded_file($this->FILE_INFO['TEMP_NAME'], $this->Connector->CONFIG['FILES_ROOT'] .
                $this->FILE_INFO['NEW_NAME'])
        ) {
            throw new Exception('Failed to move file to destination', 500);
        }

        if (!chmod($this->Connector->CONFIG['FILES_ROOT'] . $this->FILE_INFO['NEW_NAME'], 0644)) {
            throw new Exception('Failed to change file permissions', 500);
        }

        if (!$this->Connector->CONFIG['LOG_IP']) {
            $this->fingerPrintInfo['ip'] = null;
        }

        $this->Connector->newIntoDB($this->FILE_INFO, $this->fingerPrintInfo);

        return [
            'hash' => $this->FILE_INFO['SHA1'],
            'name' => $this->FILE_INFO['NAME'],
            'url' => $this->Connector->CONFIG['FILES_URL'] . '/' . $this->FILE_INFO['NEW_NAME'],
            'size' => $this->FILE_INFO['SIZE']
        ];
    }

    public function fingerPrint($files_amount): void
    {
        $this->fingerPrintInfo = [
            'timestamp' => time(),
            'useragent' => $_SERVER['HTTP_USER_AGENT'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'ip_hash' => hash('sha1', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']),
            'files_amount' => $files_amount
        ];
    }


    public function fileMIME($file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($finfo, $file['tmp_name']);
    }

    public function fileExtension($file): ?string
    {
        $extension = explode('.', $file['name']);
        if (substr_count($file['name'], '.') > 0) {
            return end($extension);
        } else {
            return null;
        }
    }


    /**
     * @throws Exception
     */
    public function checkMimeBlacklist(): void
    {
        if (in_array($this->FILE_INFO['MIME'], $this->Connector->CONFIG['BLOCKED_MIME'])) {
            throw new Exception('Filetype not allowed.', 415);
        }
    }

    /**
     * Check if file extension is blacklisted
     * if it does throw an exception.
     *
     * @throws Exception
     */
    public function checkExtensionBlacklist(): void
    {
        if (in_array($this->FILE_INFO['EXTENSION'], $this->Connector->CONFIG['BLOCKED_EXTENSIONS'])) {
            throw new Exception('Filetype not allowed.', 415);
        }
    }

    /**
     * @throws Exception
     */
    public function generateName($extension, $hash): string
    {
        $a = $this->Connector->antiDupe($hash);
        if ($a === true) {
            do {
                if ($this->Connector->CONFIG['FILES_RETRIES'] === 0) {
                    throw new Exception('Gave up trying to find an unused name!', 500);
                }

                $NEW_NAME = '';
                for ($i = 0; $i < $this->Connector->CONFIG['NAME_LENGTH']; ++$i) {
                    $NEW_NAME .= $this->Connector->CONFIG['ID_CHARSET']
                    [mt_rand(0, strlen($this->Connector->CONFIG['ID_CHARSET']))];
                }

                if (!is_null($extension)) {
                    $NEW_NAME .= '.' . $extension;
                }
            } while ($this->Connector->dbCheckNameExists($NEW_NAME) > 0);
            return $NEW_NAME;
        } else {
            return $a;
        }
    }
}
