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
use Core\Settings as Settings;

class Upload
{

    public static string $FILE_NAME;
    public static string $FILE_EXTENSION;
    public static string $FILE_MIME;
    public static string $SHA1;
    public static string $NEW_NAME;
    public static string $NEW_NAME_FULL;
    public static string $IP;

    public static string $FILE_SIZE;
    public static string $TEMP_FILE;


    public function reFiles($files): array
    {
        $result = [];
        $files = self::diverseArray($files);

        foreach ($files as $file) {
            self::$FILE_NAME = $file['name'];
            self::$FILE_SIZE = $file['size'];
            self::$TEMP_FILE = $file['tmp_name'];
            $result[] = [self::$FILE_NAME, self::$FILE_SIZE, self::$TEMP_FILE];
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
        (new Settings())->loadConfig();

        if (Settings::$ANTI_DUPE) {
            (new Database())->antiDupe();
        }

        (new Upload())->generateName();


        if (!is_dir(Settings::$FILES_ROOT)) {
            throw new Exception('File storage path not accessible.', 500);
        }

        if (!move_uploaded_file(self::$TEMP_FILE, Settings::$FILES_ROOT . self::$NEW_NAME_FULL)) {
            throw new Exception('Failed to move file to destination', 500);
        }

        if (!chmod(Settings::$FILES_ROOT . self::$NEW_NAME_FULL, 0644)) {
            throw new Exception('Failed to change file permissions', 500);
        }

        (new Database())->newIntoDB();

        if (Settings::$SSL) {
            $preURL = 'https://';
        } else {
            $preURL = 'http://';
        }

        return [
            'hash' => self::$SHA1,
            'name' => self::$FILE_NAME,
            'url' => $preURL . Settings::$URL . '/' . rawurlencode(self::$NEW_NAME_FULL),
            'size' => self::$FILE_SIZE
        ];
    }

    /**
     * @throws Exception
     */
    public function generateName(): string
    {
        (new Upload())->fileInfo();

        do {
            if (Settings::$FILES_RETRIES === 0) {
                throw new Exception('Gave up trying to find an unused name!', 500);
            }

            self::$NEW_NAME = '';
            for ($i = 0; $i < Settings::$NAME_LENGTH; ++$i) {
                self::$NEW_NAME .= Settings::$ID_CHARSET[mt_rand(0, strlen(Settings::$ID_CHARSET))];
            }

            if (isset(self::$FILE_EXTENSION) && self::$FILE_EXTENSION !== '') {
                self::$NEW_NAME_FULL = self::$NEW_NAME . '.' . self::$FILE_EXTENSION;
            }

            if (Settings::$BLACKLIST_DB) {
                (new Database())->checkFileBlacklist();
            }

            if (Settings::$FILTER_MODE) {
                self::checkMimeBlacklist();
                self::checkExtensionBlacklist();
            }
        } while ((new Database())->dbCheckNameExists() > 0);

        return self::$NEW_NAME_FULL;
    }

    public function fileInfo()
    {
        if (isset($_FILES['files'])) {
            self::$SHA1 = sha1_file(self::$TEMP_FILE);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            self::$FILE_MIME = finfo_file($finfo, self::$TEMP_FILE);
            finfo_close($finfo);

            if (Settings::$LOG_IP) {
                self::$IP = $_SERVER['REMOTE_ADDR'];
            } else {
                self::$IP = '0';
            }

            foreach (Settings::$DOUBLE_DOTS as $DDOT) {
                if (stripos(strrev(self::$FILE_NAME), $DDOT) === 0) {
                    self::$FILE_EXTENSION = strrev($DDOT);
                } else {
                    self::$FILE_EXTENSION = pathinfo(self::$FILE_NAME, PATHINFO_EXTENSION);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function checkMimeBlacklist()
    {
        if (in_array(self::$FILE_MIME, Settings::$BLOCKED_MIME)) {
            throw new Exception('Filetype not allowed.', 415);
        }
    }

    /**
     * @throws Exception
     */
    public function checkExtensionBlacklist()
    {
        if (in_array(self::$FILE_EXTENSION, Settings::$BLOCKED_EXTENSIONS)) {
            throw new Exception('Filetype not allowed.', 415);
        }
    }
}
