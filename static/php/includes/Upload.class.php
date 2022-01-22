<?php
/*
 * Uguu
 *
 * @copyright Copyright (c) 2022 Go Johansson (nekunekus) <neku@pomf.se> <github.com/nokonoko>
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


require_once 'Core.namespace.php';

use Core\Database as Database;
use Core\Response as Response;
use Core\Settings as Settings;

class Upload
{
    public static string $FILE_NAME;
    public static string $FILE_EXTENSION;
    public static string $FILE_MIME;
    public static string $SHA1;
    public static int $FILE_SIZE;
    public static string $NEW_NAME;
    public static string $NEW_NAME_FULL;
    public static string $IP;
    public mixed $file;

    /**
     * @param $file
     *
     * @return array
     */
    public function uploadFile($file): array
    {
        if (Settings::$ANTI_DUPE) {
            (new Database())->antiDupe();
        }

        self::generateName($file);

        if (!move_uploaded_file($file->tempfile, Settings::$FILES_ROOT . self::$NEW_NAME_FULL)) {
            (new Response())->returnError('500', 'Failed to move file to destination', self::$FILE_NAME);
        }

        if (!chmod(Settings::$FILES_ROOT . self::$NEW_NAME_FULL, 0644)) {
            (new Response())->returnError('500', 'Failed to change file permissions', self::$FILE_NAME);
        }

        (new Database())->newIntoDB();

        return array(
            'hash' => self::$SHA1,
            'name' => self::$FILE_NAME,
            'url' => Settings::$URL . rawurlencode(self::$NEW_NAME_FULL),
            'size' => self::$FILE_SIZE
        );
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function generateName($file): string
    {
        self::fileInfo($file);

        do {
            // Iterate until we reach the maximum number of retries
            if (Settings::$FILES_RETRIES === 0) {
                (new Response())->returnError('500', 'Gave up trying to find an unused name', self::$FILE_NAME);
            }

            for ($i = 0; $i < Settings::$NAME_LENGTH; ++$i) {
                self::$NEW_NAME .= Settings::$ID_CHARSET[mt_rand(0, strlen(Settings::$ID_CHARSET))];
            }

            // Add the extension to the file name
            if (isset(self::$FILE_EXTENSION) && self::$FILE_EXTENSION !== '') {
                self::$NEW_NAME_FULL = self::$NEW_NAME . '.' . self::$FILE_EXTENSION;
            }

            // Check if the file hash is blacklisted
            if (Settings::$BLACKLIST_DB) {
                (new Database())->checkFileBlacklist();
            }

            // Check if extension or mime is blacklisted
            if (Settings::$FILTER_MODE) {
                self::checkMimeBlacklist();
                self::checkExtensionBlacklist();
            }
        } while ((new Database())->dbCheckNameExists() > 0);

        return self::$NEW_NAME_FULL;
    }

    /**
     * @param $file
     *
     * @return void
     */
    public function fileInfo($file)
    {
        if (isset($_FILES['files'])) {
            self::$FILE_NAME = $file->name;
            self::$SHA1 = sha1_file($file->tempfile);
            self::$FILE_SIZE = $file->size;
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            self::$FILE_MIME = finfo_file($finfo, $file->tempfile);
            finfo_close($finfo);

            if (Settings::$LOG_IP) {
                self::$IP = $_SERVER['REMOTE_ADDR'];
            } else {
                self::$IP = null;
            }
            // Check if extension is a double-dot extension and, if true, override $ext
            foreach (Settings::$DOUBLE_DOTS as $ddot) {
                if (stripos(strrev(self::$FILE_NAME), $ddot) === 0) {
                    self::$FILE_EXTENSION = strrev($ddot);
                } else {
                    self::$FILE_EXTENSION = pathinfo($file->name, PATHINFO_EXTENSION);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function checkMimeBlacklist()
    {
        if (in_array(self::$FILE_MIME, Settings::$BLOCKED_MIME)) {
            (new Response())->returnError('415', 'Filetype not allowed!', self::$FILE_NAME);
        }
    }

    /**
     * @return void
     */
    protected function checkExtensionBlacklist()
    {
        if (in_array(self::$FILE_EXTENSION, Settings::$BLOCKED_EXTENSIONS)) {
            (new Response())->returnError('415', 'Filetype not allowed!', self::$FILE_NAME);
        }
    }

    /**
     * @param $files
     *
     * @return array
     */
    public function reFiles($files): array
    {
        $result = [];
        $files = self::diverseArray($files);

        foreach ($files as $file) {
            $f = $this->file;
            $f->name = $file['name'];
            $f->mime = $file['type'];
            $f->size = $file['size'];
            $f->tempfile = $file['tmp_name'];
            $f->error = $file['error'];
            $result[] = $f;
        }
        return $result;
    }

    /**
     * @param $files
     *
     * @return array
     */
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
}