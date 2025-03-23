<?php

/*
 * Uguu
 *
 * @copyright Copyright (c) 2022-2025 Go Johansson (nokonoko) <neku@pomf.se>
 * @links
 *
 * Note that this was previously distributed under the MIT license 2015-2022.
 *
 * If you are a company that wants to use Uguu I urge you to contact me to
 * solve any potential license issues rather then using pre-2022 code.
 *
 * A special thanks goes out to the open source community around the world
 * for supporting and being the backbone of projects like Uguu.
 *
 * This project can be found at <https://github.com/nokonoko/Uguu>.
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

/**
 * Takes the amount of files that are being uploaded, and creates a fingerprint of the user's IP address,
 * user agent, and the amount of files being uploaded.
 *
 * @param int $files_amount The amount of files that are being uploaded.
 */

namespace Pomf\Uguu\Classes;

class Upload extends Response
{
    public array $FILE_INFO;
    public array $fingerPrintInfo;
    private mixed $Connector;

    /**
     * Resolves and processes an array of files, performing various checks and operations on each file.
     *
     * Check if the file is a dupe (if enabled).
     * Generate a new name (if not a dupe).
     * Generate hash of the file.
     * Get the extension of the file.
     * Get the MIME of the file.
     * Get the size of the file.
     *
     * @param array $files An array of file data. Each element should be an associative array
     *                     *             with the following keys:
     *                     *             - 'tmp_name' : The temporary name of the uploaded file.
     *                     *             - 'name'     : The original name of the file.
     *                     *             - 'size'     : The size of the file in bytes.
     *
     * @return array An array containing information about each uploaded file. Each element of the array
     * *             is an associative array with the following keys:
     * *             - 'temp_name' : The temporary name of the uploaded file.
     * *             - 'name'      : The processed name of the file after checking for length and removing tags.
     * *             - 'size'      : The size of the uploaded file in bytes.
     * *             - 'xxh'       : The xxhash of the uploaded file.
     * *             - 'extension' : The file extension.
     * *             - 'mime'      : The MIME type of the file.
     * *             - 'dupe'      : Indicates if the uploaded file is a duplicate.
     * *             - 'filename'  : The final filename of the uploaded file.
     */
    public function reFiles(array $files): array
    {
        $this->Connector = new Connector();
        $result = [];
        $files = $this->transposeArray($files);
        foreach ($files as $file) {
            $this->FILE_INFO = [
               'TEMP_NAME' => $file['tmp_name'],
               'NAME'      => strip_tags($this->checkNameLength($file['name'])),
               'SIZE'      => $file['size'],
               'XXH'       => hash_file('xxh3', $file['tmp_name']),
               'EXTENSION' => $this->fileExtension($file),
               'MIME'      => $this->fileMIME($file),
               'DUPE'      => false,
               'FILENAME'  => null,
            ];
            // Check if anti dupe is enabled
            if ($this->Connector->CONFIG['ANTI_DUPE']) {
                // Check if hash exists in DB, if it does return the name of the file
                $dupeResult = $this->Connector->antiDupe($this->FILE_INFO['XXH']);
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
               $this->FILE_INFO['XXH'],
               $this->FILE_INFO['EXTENSION'],
               $this->FILE_INFO['MIME'],
               $this->FILE_INFO['DUPE'],
               $this->FILE_INFO['FILENAME'],
            ];
        }
        return $result;
    }

    /**
     * Transposes a 2-dimensional array.
     *
     * Transposes the given 2-dimensional array, where the rows of the input array
     * become the columns of the transposed array.
     *
     * @param array $inputArray The input 2-dimensional array to transpose.
     *
     * @return array The transposed array, with exchanged keys of the first and second level.
     */
    public function transposeArray(array $inputArray): array
    {
        $transposedArray = [];
        foreach ($inputArray as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $transposedArray[$columnIndex][$rowIndex] = $value;
            }
        }
        return $transposedArray;
    }

    /**
     * Performs various checks (if enabled), insert info into database, moves file to storage
     * location, then returns an array of file information.
     *
     * If a check is triggered or another error occurs it will return an error stating why
     * the file was unable to be uploaded.
     *
     * @return array An array containing the following information:
     *               - hash     : The hash value of the uploaded file
     *               - name     : The name of the uploaded file
     *               - filename : The filename of the uploaded file
     *               - url      : The URL of the uploaded file
     *               - size     : The size of the uploaded file
     *               - dupe     : Boolean indicating whether the file is a duplicate
     */
    public function uploadFile(): array
    {
        if ($this->Connector->CONFIG['RATE_LIMIT']) {
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
        }
        if ($this->Connector->CONFIG['BLACKLIST_DB']) {
            $this->Connector->checkFileBlacklist($this->FILE_INFO['XXH']);
        }
        if ($this->Connector->CONFIG['FILTER_MODE'] && empty($this->FILE_INFO['EXTENSION'])) {
            $this->checkMimeBlacklist();
        }
        if ($this->Connector->CONFIG['FILTER_MODE'] && !empty($this->FILE_INFO['EXTENSION'])) {
            $this->checkMimeBlacklist();
            $this->checkExtensionBlacklist();
        }
        if (!$this->Connector->CONFIG['FILTER_MODE'] && empty($this->FILE_INFO['EXTENSION'])) {
            $this->checkMimeWhitelist();
        }
        if (!$this->Connector->CONFIG['FILTER_MODE'] && !empty($this->FILE_INFO['EXTENSION'])) {
            $this->checkMimeWhitelist();
            $this->checkExtensionWhitelist();
        }
        // If its not a dupe then skip checking if file can be written and
        // skip inserting it into the DB.
        if (!$this->FILE_INFO['DUPE']) {
            if (!is_dir($this->Connector->CONFIG['FILES_ROOT'])) {
                $this->Connector->response->error(500, 'File storage path not accessible.');
            }
            if (!is_writable($this->Connector->CONFIG['FILES_ROOT'])) {
                $this->Connector->response->error(500, 'File storage path not writeable.');
            }
            if (!$this->Connector->CONFIG['BENCHMARK_MODE']) {
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
            }
            $this->Connector->newIntoDB($this->FILE_INFO, $this->fingerPrintInfo);
        }
        return [
           'hash'     => $this->FILE_INFO['XXH'],
           //'name'     => $this->FILE_INFO['NAME'],
           'filename' => $this->FILE_INFO['FILENAME'],
           'url'      => 'https://' . $this->Connector->CONFIG['FILE_DOMAIN'] . '/' . $this->FILE_INFO['FILENAME'],
           'size'     => $this->FILE_INFO['SIZE'],
           'dupe'     => $this->FILE_INFO['DUPE'],
        ];
    }

    /**
     * Takes the amount of files that are being uploaded, and creates a fingerprint of the user's IP address,
     * user agent, and the amount of files being uploaded.
     *
     * @param $files_amount int The amount of files that are being uploaded.
     *
     */
    public function fingerPrint(int $files_amount): void
    {
        $USER_AGENT = htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? null, ENT_QUOTES, 'UTF-8');
        $CLIENT_IP = filter_var(
            $_SERVER['HTTP_CF_CONNECTING_IP'] ??
               $_SERVER['HTTP_X_REAL_IP'] ??
               $_SERVER['REMOTE_ADDR'] ??
               null,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_NULL_ON_FAILURE
        );
        $ip = null;
        if ($this->Connector->CONFIG['LOG_IP']) {
            $ip = $CLIENT_IP;
        }
        $this->fingerPrintInfo = [
           'timestamp'    => $this->Connector->currentTime,
           'useragent'    => $USER_AGENT,
           'ip'           => $ip,
           'ip_hash'      => hash('xxh3', $CLIENT_IP . $USER_AGENT),
           'files_amount' => $files_amount,
        ];
    }

    /**
     * Returns the MIME type of a file
     *
     * @param $file array The file to be checked.
     *
     * @return string The MIME type of the file.
     */
    public function fileMIME(array $file): string
    {
        $FILE_INFO = finfo_open(FILEINFO_MIME_TYPE);
        return finfo_file($FILE_INFO, $file['tmp_name']);
    }

    /**
     * Determines the double dot file extension from the given file.
     *
     * If the last two elements of the array contain double dots, those will be extracted and concatenated.
     * If the resulting double-dot extension is present in the whitelist, it will be returned.
     * Otherwise, the last element of the array will be returned.
     *
     * @param array $extension An array of strings representing file extensions.
     *
     * @return string The extracted extension.
     */
    public function doubleDotExtension(array $extension): string
    {
        $doubleDotArray = array_slice($extension, -2, 2);
        $doubleDot = strtolower(preg_replace('/[^a-zA-Z.]/', '', join('.', $doubleDotArray)));
        if (in_array($doubleDot, $this->Connector->CONFIG['DOUBLE_DOTS_EXTENSIONS'])) {
            return $doubleDot;
        }
        return end($extension);
    }

    /**
     * Determines the file extension from the given file.
     *
     * The method checks if the file name contains a dot (.). If it does, the file name is split
     * using the dot as the delimiter to extract the extension. The number of dots in the file name
     * is also counted to handle special cases.
     *
     * If the file name contains exactly two dots, the method calls the doubleDotExtension() function
     * to handle the special case. Otherwise, the method returns the last element of the exploded
     * file name array, which represents the extension.
     *
     * @param array $file The file array containing the name of the file.
     *
     * @return string|bool The file extension if it exists, or false if the file name does not contain a dot.
     */
    public function fileExtension(array $file): string|bool
    {
        if (str_contains($file['name'], '.')) {
            $extension = explode('.', $file['name']);
            $dotCount = substr_count($file['name'], '.');
            return match ($dotCount) {
                2 => $this->doubleDotExtension($extension),
                default => end($extension)
            };
        }
        return false;
    }

    /**
     * Checks if the MIME type of the uploaded file is in the blacklist.
     *
     * If the MIME is in the blacklist, an error is returned indicating that the filetype
     * is not allowed.
     *
     */
    public function checkMimeBlacklist(): void
    {
        if (in_array($this->FILE_INFO['MIME'], $this->Connector->CONFIG['FILTER_MIME'])) {
            $this->Connector->response->error(415, 'Filetype not allowed');
        }
    }

    /**
     * Checks if the MIME type of the uploaded file is in the whitelist.
     *
     * If the MIME type is not in the whitelist, an error is returned indicating that the filetype
     * is not allowed.
     *
     */
    public function checkMimeWhitelist(): void
    {
        if (!in_array($this->FILE_INFO['MIME'], $this->Connector->CONFIG['FILTER_MIME'])) {
            $this->Connector->response->error(415, 'Filetype not allowed');
        }
    }

    /**
     * Checks if the extension of the uploaded file is in the blacklist.
     *
     * If the extension is in the blacklist, an error is returned indicating that the filetype
     * is not allowed.
     *
     */
    public function checkExtensionBlacklist(): void
    {
        if (in_array($this->FILE_INFO['EXTENSION'], $this->Connector->CONFIG['FILTER_EXTENSIONS'])) {
            $this->Connector->response->error(415, 'Filetype not allowed');
        }
    }

    /**
     * Checks if the extension of the uploaded file is in the whitelist.
     *
     * If the extension is not in the whitelist, an error is returned indicating that the filetype
     * is not allowed.
     *
     */
    public function checkExtensionWhitelist(): void
    {
        if (!in_array($this->FILE_INFO['EXTENSION'], $this->Connector->CONFIG['FILTER_EXTENSIONS'])) {
            $this->Connector->response->error(415, 'Filetype not allowed');
        }
    }

    /**
     * Checks if the length of the given filename exceeds 250 characters.
     *
     * If the length of the filename exceeds 250 characters, it is truncated to a maximum of 250 characters.
     * Otherwise, the filename remains unchanged.
     *
     * @param string $fileName The filename to check the length for.
     *
     * @return string The filename, either unchanged or truncated if its length exceeds 250 characters.
     */
    public function checkNameLength(string $fileName): string
    {
        if (strlen($fileName) > 250) {
            return substr($fileName, 0, 250);
        }
        return $fileName;
    }

    /**
     * Generates a unique name for a file.
     *
     * This method generates a random name for a file by selecting characters from the ID_CHARSET
     * defined in the Connector's CONFIG. If an extension is provided, it appends the extension
     * to the generated name. The method then checks if the generated name already exists in the
     * database using the dbCheckNameExists() function. If the generated name
     * already exists, it generates a new name until a unique one is found. If the maximum number
     * of retries is reached, an error is returned.
     *
     * @param string $extension The extension of the file.
     *
     * @return string The generated unique name for the file.
     */
    public function generateName(string $extension): string
    {
        do {
            if ($this->Connector->CONFIG['FILES_RETRIES'] === 0) {
                $this->Connector->response->error(500, 'Gave up trying to find an unused name!');
            }
            $NEW_NAME = $this->Connector->randomizer->getBytesFromString(
                $this->Connector->CONFIG['ID_CHARSET'],
                $this->Connector->CONFIG['NAME_LENGTH'],
            );
            if ($extension) {
                $NEW_NAME .= '.' . $extension;
            }
            $this->Connector->CONFIG['FILES_RETRIES']--;
        } while ($this->Connector->dbCheckNameExists($NEW_NAME));
        return $NEW_NAME;
    }
}
