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
require_once 'Database.class.php';

class Upload extends Database, errorReport
{
    public $FILE_NAME;
    public $FILE_EXTENSION;
    public $FILE_MIME;

    public $NEW_NAME;
    public $NEW_NAME_FULL;

    public function fileInfo ($file)
    {
        if (isset($_FILES['files'])) {
            $this->FILE_NAME = '';
            $this->FILE_NAME = $file->name;
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->FILE_MIME = finfo_file($finfo, $file->tempfile);
            finfo_close($finfo);

            // Check if extension is a double-dot extension and, if true, override $ext
            foreach ($this->DOUBLE_DOTS as $ddot) {
                if (stripos(strrev($this->FILE_NAME), $ddot) === 0) {
                    $this->FILE_EXTENSION = strrev($ddot);
                } else {
                    $this->FILE_EXTENSION = pathinfo($file->name, PATHINFO_EXTENSION);
                }
            }
        }
    }

public function checkFileBlacklist ($hash){
    $q = $this->db->prepare('SELECT hash, COUNT(*) AS count FROM blacklist WHERE hash = (:hash)');
    $q->bindValue(':hash', $hash, PDO::PARAM_STR);
    $q->execute();
    $result = $q->fetch();
    if ($result['count'] > 0) {
        http_response_code(415);
        throw new Exception(
            'File blacklisted!',
            415
        );
        exit(0);
    }
}

public function checkExtensionBlacklist($ext){
    //Check if EXT is blacklisted
    if (in_array($ext, unserialize(CONFIG_BLOCKED_EXTENSIONS))) {
        http_response_code(415);
        throw new Exception(
            'File type not allowed!',
            415
        );
        exit(0);
    }
}

public function checkMimeBlacklist($mime){
    //check if MIME is blacklisted
    if (in_array($mime, unserialize($this->BLOCKED_MIME))) {
        http_response_code(415);
        throw new Exception(
            'File type not allowed!',
            415
        );
        exit(0);
    }
}

    public function generateName($file)
    {
        $this->fileInfo($file);
        $error = new
        do {
            // Iterate until we reach the maximum number of retries
            if ($this->FILES_RETRIES-- === 0) {
                $error->throwError('500', 'Gave up trying to find an unused name', true);
            }




            for ($i = 0; $i < $this->NAME_LENGTH; ++$i) {
                $this->NEW_NAME .= $this->ID_CHARSET[mt_rand(0, strlen($this->ID_CHARSET))];
            }

            // Add the extension to the file name
            if (isset($this->FILE_EXTENSION) && $this->FILE_EXTENSION !== '') {
                $this->NEW_NAME_FULL = $this->NEW_NAME.'.'.$this->FILE_EXTENSION;
            }

            // Check if the file hash is blacklisted
            if($this->BLACKLIST_DB){
                $this->checkFileBlacklist($file->getSha1());
            }

            // Check if extension or mime is blacklisted
            if($this->FILTER_MODE) {
                $this->checkMimeBlacklist($this->FILE_MIME);
                $this->checkExtensionBlacklist($this->FILE_EXTENSION);
            }

            // Check if a file with the same name does already exist in the database
            $q = $db->prepare('SELECT COUNT(filename) FROM files WHERE filename = (:name)');
            $q->bindValue(':name', $name, PDO::PARAM_STR);
            $q->execute();
            $result = $q->fetchColumn();
            // If it does, generate a new name
        } while ($result > 0);

        return $name;
    }
}