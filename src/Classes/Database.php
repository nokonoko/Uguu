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
use PDO;

class Database
{
    private PDO $DB;

    /**
     * Sets the value of the DB variable.
     *
     * @param $DB PDO The database connection.
     */
    public function setDB(PDO $DB): void
    {
        $this->DB = $DB;
    }
    
    /**
     * Checks if a file name exists in the database
     *
     * @param $name string The name of the file.
     *
     * @return int The number of rows that match the query.
     * @throws \Exception
     */
    public function dbCheckNameExists(string $name): int
    {
        try {
            $q = $this->DB->prepare('SELECT COUNT(filename) FROM files WHERE filename = (:name)');
            $q->bindValue(':name', $name);
            $q->execute();
            return $q->fetchColumn();
        } catch (Exception) {
            throw new Exception('Cant check if name exists in DB.', 500);
        }
    }
    
    /**
     * Checks if the file is blacklisted
     *
     * @param $FILE_INFO array An array containing the following:
     *
     * @throws \Exception
     */
    public function checkFileBlacklist(array $FILE_INFO): void
    {
        try {
            $q = $this->DB->prepare('SELECT hash, COUNT(*) AS count FROM blacklist WHERE hash = (:hash)');
            $q->bindValue(':hash', $FILE_INFO['SHA1']);
            $q->execute();
            $result = $q->fetch();
            if ($result['count'] > 0) {
                throw new Exception('File blacklisted!', 415);
            }
        } catch (Exception) {
            throw new Exception('Cant check blacklist DB.', 500);
        }
    }
    
    /**
     * Checks if the file already exists in the database
     *
     * @param $hash string The hash of the file you want to check for.
     *
     * @throws \Exception
     */
    public function antiDupe(string $hash): bool | array | string
    {
        if (!$this->CONFIG['ANTI_DUPE']) {
            return true;
        }

        try {
            $q = $this->DB->prepare(
                'SELECT filename, COUNT(*) AS count FROM files WHERE hash = (:hash)'
            );
            $q->bindValue(':hash', $hash);
            $q->execute();
            $result = $q->fetch();
            if ($result['count'] > 0) {
                return $result['filename'];
            } else {
                return true;
            }
        } catch (Exception) {
            throw new Exception('Cant check for dupes in DB.', 500);
        }
    }
    
    /**
     * Inserts a new file into the database
     *
     * @param $FILE_INFO       array
     * @param $fingerPrintInfo array
     *
     * @throws \Exception
     */
    public function newIntoDB(array $FILE_INFO, array $fingerPrintInfo): void
    {
        try {
            $q = $this->DB->prepare(
                'INSERT INTO files (hash, originalname, filename, size, date, ip)' .
                'VALUES (:hash, :orig, :name, :size, :date, :ip)'
            );
            $q->bindValue(':hash', $FILE_INFO['SHA1']);
            $q->bindValue(':orig', $FILE_INFO['NAME']);
            $q->bindValue(':name', $FILE_INFO['NEW_NAME']);
            $q->bindValue(':size', $FILE_INFO['SIZE'], PDO::PARAM_INT);
            $q->bindValue(':date', $fingerPrintInfo['timestamp']);
            $q->bindValue(':ip', $fingerPrintInfo['ip']);
            $q->execute();
        } catch (Exception) {
            throw new Exception('Cant insert into DB.', 500);
        }
    }


    /**
     * Creates a new row in the database with the information provided
     *
     * @param $fingerPrintInfo array
     */
    public function createRateLimit(array $fingerPrintInfo): void
    {
        $q = $this->DB->prepare(
            'INSERT INTO timestamp (iphash, files, time)' .
            'VALUES (:iphash, :files, :time)'
        );

        $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
        $q->bindValue(':files', $fingerPrintInfo['files_amount']);
        $q->bindValue(':time', $fingerPrintInfo['timestamp']);
        $q->execute();
    }

    /**
     * Update the rate limit table with the new file count and timestamp
     *
     * @param $fCount          int The number of files uploaded by the user.
     * @param $iStamp          boolean A boolean value that determines whether or not to update the timestamp.
     * @param $fingerPrintInfo array An array containing the following keys:
     */
    public function updateRateLimit(int $fCount, bool $iStamp, array $fingerPrintInfo): void
    {
        if ($iStamp) {
            $q = $this->DB->prepare(
                'UPDATE ratelimit SET files = (:files), time = (:time) WHERE iphash = (:iphash)'
            );
            $q->bindValue(':time', $fingerPrintInfo['timestamp']);
        } else {
            $q = $this->DB->prepare(
                'UPDATE ratelimit SET files = (:files) WHERE iphash = (:iphash)'
            );
        }

        $q->bindValue(':files', $fCount);
        $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
        $q->execute();
    }



    /**
     * Checks if the user has uploaded more than 100 files in the last minute, if so it returns true, if not it updates the database with the new file count and
     * timestamp
     *
     * @param $fingerPrintInfo array An array containing the following:
     *
     * @return bool A boolean value.
     */
    public function checkRateLimit(array $fingerPrintInfo): bool
    {
        $q = $this->DB->prepare(
            'SELECT files, time, iphash, COUNT(*) AS count FROM ratelimit WHERE iphash = (:iphash)'
        );
        $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
        $q->execute();
        $result = $q->fetch();

        $nTime = $fingerPrintInfo['timestamp'] - (60);

        switch (true) {
            //If more then 100 files trigger rate-limit
            case $result['files'] > 100:
                return true;

                //if timestamp is older than one minute, set new files count and timestamp
            case $result['time'] < $nTime:
                $this->updateRateLimit($fingerPrintInfo['files_amount'], true, $fingerPrintInfo);
                break;

                //if timestamp isn't older than one-minute update the files count
            case $result['time'] > $nTime:
                $this->updateRateLimit($fingerPrintInfo['files_amount'] + $result['files'], false, $fingerPrintInfo);
                break;

                //If there is no other match a record does not exist, create one
            default:
                $this->createRateLimit($fingerPrintInfo);
                break;
        }
        return false;
    }
}
