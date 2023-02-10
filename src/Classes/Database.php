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

    use DateTimeZone;
    use Exception;
    use PDO;
    use DateTime;

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
     * @return bool The number of rows that match the query.
     * @throws \Exception
     */
    public function dbCheckNameExists(string $name): bool
    {
        try {
            $q = $this->DB->prepare('SELECT * FROM files WHERE EXISTS
                                            (SELECT filename FROM files WHERE filename = (:name)) LIMIT 1');
            $q->bindValue(':name', $name);
            $q->execute();
            $result = $q->fetch();
            if ($result) {
                return true;
            }
            return false;
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
            $q = $this->DB->prepare('SELECT * FROM blacklist WHERE EXISTS
                                            (SELECT hash FROM blacklist WHERE hash = (:hash)) LIMIT 1');
            $q->bindValue(':hash', $FILE_INFO['SHA1']);
            $q->execute();
            $result = $q->fetch();
            if ($result) {
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
    public function antiDupe(string $hash): array
    {
        try {
            $q = $this->DB->prepare(
                'SELECT * FROM files WHERE EXISTS
                        (SELECT filename FROM files WHERE hash = (:hash)) LIMIT 1',
            );
            $q->bindValue(':hash', $hash);
            $q->execute();
            $result = $q->fetch();
            if ($result) {
                return [
                'result' => true,
                'name' => $result['filename'],
                ];
            } else {
                return [
                   'result' => false
                ];
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
                'VALUES (:hash, :orig, :name, :size, :date, :ip)',
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
     *
     * @throws \Exception
     */
    public function createRateLimit(array $fingerPrintInfo): void
    {
        try {
            $q = $this->DB->prepare(
                'INSERT INTO ratelimit (iphash, files, time)' .
                'VALUES (:iphash, :files, :time)',
            );
            $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
            $q->bindValue(':files', $fingerPrintInfo['files_amount']);
            $q->bindValue(':time', $fingerPrintInfo['timestamp']);
            $q->execute();
        } catch (Exception $e) {
            throw new Exception(500, $e->getMessage());
        }
    }

    /**
     * Update the rate limit table with the new file count and timestamp
     *
     * @param $fCount          int The number of files uploaded by the user.
     * @param $iStamp          bool A boolean value that determines whether or not to update the timestamp.
     * @param $fingerPrintInfo array An array containing the following keys:
     *
     * @throws \Exception
     */
    public function updateRateLimit(int $fCount, bool $iStamp, array $fingerPrintInfo): void
    {
        try {
            if ($iStamp) {
                $q = $this->DB->prepare(
                    'UPDATE ratelimit SET files = (:files), time = (:time) WHERE iphash = (:iphash)',
                );
                $q->bindValue(':time', $fingerPrintInfo['timestamp']);
            } else {
                $q = $this->DB->prepare(
                    'UPDATE ratelimit SET files = (:files) WHERE iphash = (:iphash)',
                );
            }
            $q->bindValue(':files', $fCount);
            $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
            $q->execute();
        } catch (Exception $e) {
            throw new Exception(500, $e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function compareTime(int $timestamp, int $seconds_d): bool
    {
        $dateTime_end = new DateTime('now', new DateTimeZone('Europe/Stockholm'));
        $dateTime_start = new DateTime();
        $dateTime_start->setTimestamp($timestamp);
        $diff = strtotime($dateTime_end->format('Y-m-d H:i:s')) - strtotime($dateTime_start->format('Y-m-d H:i:s'));
        if ($diff > $seconds_d) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the user has uploaded more than 100 files in the last minute, if so it returns true,
     * if not it updates the database with the new file
     * count and timestamp
     *
     * @param $fingerPrintInfo array An array containing the following:
     *
     * @return bool A boolean value.
     * @throws \Exception
     */
    public function checkRateLimit(array $fingerPrintInfo, int $rateTimeout, int $fileLimit): bool
    {
        $q = $this->DB->prepare(
            'SELECT files, time, iphash, COUNT(*) AS count FROM ratelimit WHERE iphash = (:iphash)',
        );
        $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
        $q->execute();
        $result = $q->fetch();

        //If there is no other match a record does not exist, create one.
        if (!$result['count'] > 0) {
            $this->createRateLimit($fingerPrintInfo);
            return false;
        }

        // Apply rate-limit when file count reached and timeout not reached.
        if ($result['files'] === $fileLimit and !$this->compareTime($result['time'], $rateTimeout)) {
            return true;
        }

        // Update timestamp if timeout reached.
        if ($this->compareTime($result['time'], $rateTimeout)) {
            $this->updateRateLimit($fingerPrintInfo['files_amount'], true, $fingerPrintInfo);
            return false;
        }

        // Add filecount, timeout not reached.
        if ($result['files'] < $fileLimit and !$this->compareTime($result['time'], $rateTimeout)) {
            $this->updateRateLimit($result['files'] + $fingerPrintInfo['files_amount'], false, $fingerPrintInfo);
            return false;
        }

        return false;
    }
}
