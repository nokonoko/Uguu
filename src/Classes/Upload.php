<?php
    /**
     * Uguu
     *
     * @copyright Copyright (c) 2022-2023 Go Johansson (nokonoko) <neku@pomf.se>
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
    
    class Upload extends Response
    {
        public array $FILE_INFO;
        public array $fingerPrintInfo;
        private mixed $Connector;
        
        /**
         * Takes an array of files, and returns an array of arrays containing the file's temporary name,
         * name, size, SHA1 hash, extension, and MIME type
         *
         * @param $files array The files array from the $_FILES superglobal.
         *
         * @return array An array of arrays.
         */
        public function reFiles(array $files):array
        {
            $this->Connector = new Connector();
            $result = [];
            $files = $this->diverseArray($files);
            foreach ($files as $file) {
                $this->FILE_INFO = [
                   'TEMP_NAME' => $file['tmp_name'],
                   'NAME'      => strip_tags($this->checkNameLength($file['name'])),
                   'SIZE'      => $file['size'],
                   'SHA1'      => sha1_file($file['tmp_name']),
                   'EXTENSION' => $this->fileExtension($file),
                   'MIME'      => $this->fileMIME($file),
                   'DUPE'      => false,
                   'FILENAME'  => null,
                ];
                // Check if anti dupe is enabled
                if ($this->Connector->CONFIG['ANTI_DUPE']) {
                    // Check if hash exists in DB, if it does return the name of the file
                    $dupeResult = $this->Connector->antiDupe($this->FILE_INFO['SHA1']);
                    if ($dupeResult['result']) {
                        $this->FILE_INFO['FILENAME'] = $dupeResult['name'];
                        $this->FILE_INFO['DUPE'] = true;
                    }
                }
                // If its not a dupe then generate a new name
                if (!$this->FILE_INFO['DUPE']) {
                    $this->FILE_INFO['FILENAME'] = $this->generateName($this->FILE_INFO['EXTENSION']);
                }
                $result[] = [
                   $this->FILE_INFO['TEMP_NAME'],
                   $this->FILE_INFO['NAME'],
                   $this->FILE_INFO['SIZE'],
                   $this->FILE_INFO['SHA1'],
                   $this->FILE_INFO['EXTENSION'],
                   $this->FILE_INFO['MIME'],
                   $this->FILE_INFO['DUPE'],
                   $this->FILE_INFO['FILENAME'],
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
         */
        public function uploadFile():array
        {
            switch (true) {
                case $this->Connector->CONFIG['RATE_LIMIT']:
                    if (
                       $this->Connector->checkRateLimit(
                          $this->fingerPrintInfo,
                          $this->Connector->CONFIG['RATE_LIMIT_TIMEOUT'],
                          $this->Connector->CONFIG['RATE_LIMIT_FILES'],
                       )
                    ) {
                        $this->Connector->response->error(
                           500,
                           'Rate limit, please wait ' . $this->Connector->CONFIG['RATE_LIMIT_TIMEOUT'] .
                           ' seconds before uploading again.',
                        );
                    }
                // Continue
                case $this->Connector->CONFIG['BLACKLIST_DB']:
                    $this->Connector->checkFileBlacklist($this->FILE_INFO['SHA1']);
                // Continue
                case $this->Connector->CONFIG['FILTER_MODE'] && empty($this->FILE_INFO['EXTENSION']):
                    $this->checkMimeBlacklist();
                // Continue
                case $this->Connector->CONFIG['FILTER_MODE'] && !empty($this->FILE_INFO['EXTENSION']):
                    $this->checkMimeBlacklist();
                    $this->checkExtensionBlacklist();
                // Continue
            }
            // If its not a dupe then skip checking if file can be written and
            // skip inserting it into the DB.
            if (!$this->FILE_INFO['DUPE']) {
                if (!is_dir($this->Connector->CONFIG['FILES_ROOT'])) {
                    $this->Connector->response->error(500, 'File storage path not accessible.');
                }
                if (
                   !move_uploaded_file(
                      $this->FILE_INFO['TEMP_NAME'],
                      $this->Connector->CONFIG['FILES_ROOT'] .
                      $this->FILE_INFO['FILENAME'],
                   )
                ) {
                    $this->Connector->response->error(500, 'Failed to move file to destination.');
                }
                if (!chmod($this->Connector->CONFIG['FILES_ROOT'] . $this->FILE_INFO['FILENAME'], 0644)) {
                    $this->Connector->response->error(500, 'Failed to change file permissions.');
                }
                $this->Connector->newIntoDB($this->FILE_INFO, $this->fingerPrintInfo);
            }
            return [
               'hash'     => $this->FILE_INFO['SHA1'],
               'name'     => $this->FILE_INFO['NAME'],
               'filename' => $this->FILE_INFO['FILENAME'],
               'url'      => 'https://' . $this->Connector->CONFIG['FILE_DOMAIN'] . '/' . $this->FILE_INFO['FILENAME'],
               'size'     => $this->FILE_INFO['SIZE'],
               'dupe'     => $this->FILE_INFO['DUPE'],
            ];
        }
        
        /**
         * Takes the amount of files that are being uploaded, and creates a fingerprint of the user's IP address,
         * user agent, and the amount of files being
         * uploaded
         *
         * @param $files_amount int The amount of files that are being uploaded.
         *
         */
        public function fingerPrint(int $files_amount):void
        {
            if (!empty($_SERVER['HTTP_USER_AGENT'])) {
                $USER_AGENT = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_ENCODED);
                $ip = null;
                if ($this->Connector->CONFIG['LOG_IP']) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                $this->fingerPrintInfo = [
                   'timestamp'    => time(),
                   'useragent'    => $USER_AGENT,
                   'ip'           => $ip,
                   'ip_hash'      => hash('sha1', $_SERVER['REMOTE_ADDR'] . $USER_AGENT),
                   'files_amount' => $files_amount,
                ];
            } else {
                $this->Connector->response->error(500, 'Invalid user agent.');
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
         * It takes an array of strings, and returns the last two strings joined by a dot,
         * unless the last two strings are in the array of strings in the
         * `DOUBLE_DOTS_EXTENSIONS` config variable, in which case it returns the last string
         *
         * @param $extension array The extension of the file.
         *
         * @return string The last two elements of the array are joined together and returned.
         */
        public function doubleDotExtension(array $extension):string
        {
            $doubleDotArray = array_slice($extension, -2, 2);
            $doubleDot = strtolower(preg_replace('/[^a-zA-Z.]/', '', join('.', $doubleDotArray)));
            if (in_array($doubleDot, $this->Connector->CONFIG['DOUBLE_DOTS_EXTENSIONS'])) {
                return $doubleDot;
            } else {
                return end($extension);
            }
        }
        
        /**
         * Takes a file and returns the file extension
         *
         * @param $file array The file you want to get the extension from.
         *
         * @return string The file extension of the file.
         */
        public function fileExtension(array $file):string | bool
        {
            if(str_contains($file['name'], '.')){
            $extension = explode('.', $file['name']);
            $dotCount = substr_count($file['name'], '.');    
            return match ($dotCount) {
                1 => end($extension),
                2 => $this->doubleDotExtension($extension),
                default => end($extension)
            };
            }
            return false;
        }
        
        /**
         * > Check if the file's MIME type is in the blacklist
         *
         */
        public function checkMimeBlacklist():void
        {
            if (in_array($this->FILE_INFO['MIME'], $this->Connector->CONFIG['BLOCKED_MIME'])) {
                $this->Connector->response->error(415, 'Filetype not allowed');
            }
        }
        
        /**
         * > Check if the file extension is in the blacklist
         *
         */
        public function checkExtensionBlacklist():void
        {
            if (in_array($this->FILE_INFO['EXTENSION'], $this->Connector->CONFIG['BLOCKED_EXTENSIONS'])) {
                $this->Connector->response->error(415, 'Filetype not allowed');
            }
        }
        
        public function checkNameLength(string $fileName):string
        {
            if (strlen($fileName) > 250) {
                return substr($fileName, 0, 250);
            } else {
                return $fileName;
            }
        }
        
        /**
         * Generates a random string of characters, checks if it exists in the database,
         * and if it does, it generates another one
         *
         * @param $extension string The file extension.
         *
         * @return string A string
         */
        public function generateName(string $extension):string
        {
            do {
                if ($this->Connector->CONFIG['FILES_RETRIES'] === 0) {
                    $this->Connector->response->error(500, 'Gave up trying to find an unused name!');
                }
                $NEW_NAME = '';
                for ($i = 0; $i < $this->Connector->CONFIG['NAME_LENGTH']; $i++) {
                    $index = rand(0, strlen($this->Connector->CONFIG['ID_CHARSET']) - 1);
                    $NEW_NAME .= $this->Connector->CONFIG['ID_CHARSET'][$index];
                }
                if ($extension) {
                    $NEW_NAME .= '.' . $extension;
                }
            } while ($this->Connector->dbCheckNameExists($NEW_NAME));
            return $NEW_NAME;
        }
    }
