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
         * Takes an array of files, and returns an array of arrays containing the file's temporary name, name, size, SHA1 hash, extension, and MIME type
         *
         * @param $files array The files array from the $_FILES superglobal.
         *
         * @return array An array of arrays.
         * @throws \Exception
         */
        public function reFiles(array $files):array
        {
            $this->Connector = new Connector();
            $this->Connector->setDB($this->Connector->DB);
            $result = [];
            $files = $this->diverseArray($files);
            foreach ($files as $file) {
                $hash = sha1_file($file['tmp_name']);
                $this->FILE_INFO = [
                   'TEMP_NAME' => $file['tmp_name'],
                   'NAME'      => strip_tags($file['name']),
                   'SIZE'      => $file['size'],
                   'SHA1'      => $hash,
                   'EXTENSION' => $this->fileExtension($file),
                   'MIME'      => $this->fileMIME($file),
                   'NEW_NAME'  => $this->generateName($this->fileExtension($file), $hash),
                ];
                $result[] = [
                   $this->FILE_INFO['TEMP_NAME'],
                   $this->FILE_INFO['NAME'],
                   $this->FILE_INFO['SIZE'],
                   $this->FILE_INFO['SHA1'],
                   $this->FILE_INFO['EXTENSION'],
                   $this->FILE_INFO['MIME'],
                ];
            }
            return $result;
        }
        
        /**
         * Takes an array of arrays and returns an array of arrays with the keys and values swapped
         *
         * @param $files array an array of arrays
         *
         * @return array ```
         * array:2 [▼
         *   0 => array:2 [▼
         *     'TEMP_NAME' => 'example'
         *     'NAME' => 'example'
         *     'SIZE' => 'example'
         *     'SHA1' => 'example'
         *     'EXTENSION' => 'example'
         *     'MIME' => 'example'
         *
         *   ]
         *   1 => array:2 [▼
         *     'TEMP_NAME' => 'example'
         *     'NAME' => 'example'
         *     'SIZE' => 'example'
         *     'SHA1' => 'example'
         *     'EXTENSION' => 'example'
         *     'MIME' => 'example'
         *   ]
         * ]
         * ```
         */
        public function diverseArray(array $files):array
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
         * Takes a file, checks if it's blacklisted, moves it to the file storage, and then logs it to the database
         *
         * @return array An array containing the hash, name, url, and size of the file.
         * @throws \Exception
         */
        public function uploadFile():array
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
               !move_uploaded_file(
                  $this->FILE_INFO['TEMP_NAME'],
                  $this->Connector->CONFIG['FILES_ROOT'] .
                  $this->FILE_INFO['NEW_NAME'],
               )
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
               'url'  => $this->Connector->CONFIG['FILES_URL'] . '/' . $this->FILE_INFO['NEW_NAME'],
               'size' => $this->FILE_INFO['SIZE'],
            ];
        }
        
        /**
         * Takes the amount of files that are being uploaded, and creates a fingerprint of the user's IP address, user agent, and the amount of files being
         * uploaded
         *
         * @param $files_amount int The amount of files that are being uploaded.
         *
         * @throws \Exception
         */
        public function fingerPrint(int $files_amount):void
        {
            if (!empty($_SERVER['HTTP_USER_AGENT'])) {
                $USER_AGENT = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_ENCODED);
                $this->fingerPrintInfo = [
                   'timestamp'    => time(),
                   'useragent'    => $USER_AGENT,
                   'ip'           => $_SERVER['REMOTE_ADDR'],
                   'ip_hash'      => hash('sha1', $_SERVER['REMOTE_ADDR'] . $USER_AGENT),
                   'files_amount' => $files_amount,
                ];
            } else {
                throw new Exception('Invalid user agent.', 500);
            }
        }
        
        /**
         * Returns the MIME type of a file
         *
         * @param $file array The file to be checked.
         *
         * @return string The MIME type of the file.
         */
        public function fileMIME(array $file):string
        {
            $FILE_INFO = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($FILE_INFO, $file['tmp_name']);
        }
        
        /**
         * Takes a file and returns the file extension
         *
         * @param $file array The file you want to get the extension from.
         *
         * @return ?string The file extension of the file.
         */
        public function fileExtension(array $file):?string
        {
            $extension = explode('.', $file['name']);
            if (substr_count($file['name'], '.') > 0) {
                return end($extension);
            } else {
                return null;
            }
        }
        
        /**
         * > Check if the file's MIME type is in the blacklist
         *
         * @throws \Exception
         */
        public function checkMimeBlacklist():void
        {
            if (in_array($this->FILE_INFO['MIME'], $this->Connector->CONFIG['BLOCKED_MIME'])) {
                throw new Exception('Filetype not allowed.', 415);
            }
        }
        
        /**
         * > Check if the file extension is in the blacklist
         *
         * @throws \Exception
         */
        public function checkExtensionBlacklist():void
        {
            if (in_array($this->FILE_INFO['EXTENSION'], $this->Connector->CONFIG['BLOCKED_EXTENSIONS'])) {
                throw new Exception('Filetype not allowed.', 415);
            }
        }
        
        /**
         * Generates a random string of characters, checks if it exists in the database, and if it does, it generates another one
         *
         * @param $extension string The file extension.
         * @param $hash      string The hash of the file.
         *
         * @return string A string
         * @throws \Exception
         */
        public function generateName(string $extension, string $hash):string
        {
            if ($this->Connector->antiDupe($hash)) {
                do {
                    if ($this->Connector->CONFIG['FILES_RETRIES'] === 0) {
                        throw new Exception('Gave up trying to find an unused name!', 500);
                    }
                    $NEW_NAME = '';
                    for ($i = 0; $i < $this->Connector->CONFIG['NAME_LENGTH']; ++$i) {
                        $NEW_NAME .= $this->Connector->CONFIG['ID_CHARSET']
                        [mt_rand(0, strlen($this->Connector->CONFIG['ID_CHARSET']))];
                    }
                    if (!empty($extension)) {
                        $NEW_NAME .= '.' . $extension;
                    }
                } while ($this->Connector->dbCheckNameExists($NEW_NAME) > 0);
                return $NEW_NAME;
            } else {
                return $this->Connector->antiDupe($hash);
            }
        }
    }
