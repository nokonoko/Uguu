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

namespace Core {

    use PDO;
    use Upload as Upload;

    class Settings
    {

        public static mixed $DB;

        public static string $DB_MODE;
        public static string $DB_PATH;
        public static string $DB_USER;
        public static string $DB_PASS;

        public static bool $LOG_IP;
        public static bool $ANTI_DUPE;
        public static bool $BLACKLIST_DB;
        public static bool $FILTER_MODE;

        public static string $FILES_ROOT;
        public static int $FILES_RETRIES;

        public static bool $SSL;
        public static string $URL;

        public static int $NAME_LENGTH;
        public static string $ID_CHARSET;
        public static array $DOUBLE_DOTS;
        public static array $BLOCKED_EXTENSIONS;
        public static array $BLOCKED_MIME;


        public function __construct()
        {
            $settings_array = json_decode(file_get_contents('/Users/go.johansson/PERSONAL_REPOS/Uguu/dist.json'), true);
            self::$DB_MODE = $settings_array['DB_MODE'];
            self::$DB_PATH = $settings_array['DB_PATH'];
            self::$DB_USER = $settings_array['DB_USER'];
            self::$DB_PASS = $settings_array['DB_PASS'];
            self::$LOG_IP = $settings_array['LOG_IP'];
            self::$ANTI_DUPE = $settings_array['ANTI_DUPE'];
            self::$BLACKLIST_DB = $settings_array['BLACKLIST_DB'];
            self::$FILTER_MODE = $settings_array['FILTER_MODE'];
            self::$FILES_ROOT = $settings_array['FILES_ROOT'];
            self::$FILES_RETRIES = $settings_array['FILES_RETRIES'];
            self::$SSL = $settings_array['SSL'];
            self::$URL = $settings_array['URL'];
            self::$NAME_LENGTH = $settings_array['NAME_LENGTH'];
            self::$ID_CHARSET = $settings_array['ID_CHARSET'];
            self::$BLOCKED_EXTENSIONS = $settings_array['BLOCKED_EXTENSIONS'];
            self::$BLOCKED_MIME = $settings_array['BLOCKED_MIME'];
            self::$DOUBLE_DOTS = array($settings_array['DOUBLE_DOTS']);
        }
    }

    class cuteGrills
    {
        public static array $GRILLS;

        public function __construct()
        {
            self::$GRILLS = array_slice(scandir('/Users/go.johansson/PERSONAL_REPOS/Uguu/dist/img/grills/'), 2);
        }


        public static function showGrills()
        {
            if (!headers_sent()) {
                header('Location: ' . self::$GRILLS[array_rand(self::$GRILLS)], true, 303);
            }
        }
    }

    class Response
    {
        public function returnError($code, $message, $filename): bool|string
        {
            http_response_code($code);
            header('Content-Type: application/json; charset=UTF-8');
            self::cleanAndDie();
            return json_encode(array(
                'success' => false,
                'file' => $filename,
                'code' => $code,
                'description' => $message
            ), JSON_FORCE_OBJECT);
        }

        public function cleanAndDie()
        {
            Settings::$DB = null;
        }

        public function returnSuccess($files): bool|string
        {
            http_response_code('200');
            header('Content-Type: application/json; charset=UTF-8');
            return json_encode(array(
                'success' => true,
                'files' => $files
            ), JSON_PRETTY_PRINT);
        }
    }


    class Database
    {

        public function __construct()
        {
            Settings::$DB = new PDO(
                Settings::$DB_MODE . ':' . Settings::$DB_PATH, Settings::$DB_USER,
                Settings::$DB_PASS
            );
        }

        public function dbCheckNameExists()
        {
            $q = Settings::$DB->prepare('SELECT COUNT(filename) FROM files WHERE filename = (:name)');
            $q->bindValue(':name', Upload::$NEW_NAME_FULL);
            $q->execute();
            return $q->fetchColumn();
        }

        public function checkFileBlacklist()
        {
            $q = Settings::$DB->prepare('SELECT hash, COUNT(*) AS count FROM blacklist WHERE hash = (:hash)');
            $q->bindValue(':hash', Upload::$SHA1, PDO::PARAM_STR);
            $q->execute();
            $result = $q->fetch();
            if ($result['count'] > 0) {
                (new Response())->returnError('415', 'File blacklisted!', Upload::$FILE_NAME);
            }
        }

        public function antiDupe(): ?array
        {
            $q = Settings::$DB->prepare(
                'SELECT filename, COUNT(*) AS count FROM files WHERE hash = (:hash) AND size = (:size)'
            );
            $q->bindValue(':hash', Upload::$SHA1, PDO::PARAM_STR);
            $q->bindValue(':size', Upload::$FILE_SIZE, PDO::PARAM_INT);
            $q->execute();
            $result = $q->fetch();
            if ($result['count'] > 0) {
                return array(
                    'hash' => Upload::$SHA1,
                    'name' => Upload::$FILE_NAME,
                    'url' => Settings::$URL . rawurlencode($result['filename']),
                    'size' => Upload::$FILE_SIZE
                );
            }
            return [];
        }

        public function newIntoDB()
        {
            $q = Settings::$DB->prepare(
                'INSERT INTO files (hash, originalname, filename, size, date, ip)' .
                'VALUES (:hash, :orig, :name, :size, :date, :ip)'
            );
            $q->bindValue(':hash', Upload::$SHA1, PDO::PARAM_STR);
            $q->bindValue(':orig', strip_tags(Upload::$FILE_NAME), PDO::PARAM_STR);
            $q->bindValue(':name', Upload::$NEW_NAME_FULL, PDO::PARAM_STR);
            $q->bindValue(':size', Upload::$FILE_SIZE, PDO::PARAM_INT);
            $q->bindValue(':date', time(), PDO::PARAM_STR);
            $q->bindValue(':ip', Upload::$IP, PDO::PARAM_STR);
            $q->execute();
        }
    }
}



