<?php
    /*
     * Uguu
     *
     * @copyright Copyright (c) 2022-2024 Go Johansson (nokonoko) <neku@pomf.se>
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
    
    namespace Pomf\Uguu\Classes;
    
    use PDO;
    
    class Database
    {
        public function dbCheckNameExists(string $name):bool
        {
            $query = match ($this->dbType) {
                'pgsql' => 'SELECT EXISTS(SELECT id FROM files WHERE filename = (:name)), filename FROM files WHERE filename = (:name) LIMIT 1',
                default => 'SELECT filename FROM files WHERE filename = (:name) AND EXISTS (SELECT id FROM files WHERE filename = (:name)) LIMIT 1'
            };
            $q = $this->DB->prepare($query);
            $q->bindValue(':name', $name);
            $q->execute();
            $result = $q->fetch();
            $q->closeCursor();
            if (isset($result['exists']) and $result['exists']) {
                return true;
            } elseif ($result) {
                return true;
            }
            return false;
        }
        
        public function checkFileBlacklist(string $hash):void
        {
            $query = match ($this->dbType) {
                'pgsql' => 'SELECT EXISTS(SELECT id FROM blacklist WHERE hash = (:hash)), hash FROM blacklist WHERE hash = (:hash) LIMIT 1',
                default => 'SELECT id FROM blacklist WHERE EXISTS(SELECT id FROM blacklist WHERE hash = (:hash)) LIMIT 1'
            };
            $q = $this->DB->prepare($query);
            $q->bindValue(':hash', $hash);
            $q->execute();
            $result = $q->fetch();
            $q->closeCursor();
            if (isset($result['exists']) and $result['exists']) {
                $this->response->error(415, 'File blacklisted.');
            } elseif ($result) {
                $this->response->error(415, 'File blacklisted.');
            }
        }
        
        public function antiDupe(string $hash):array
        {
            $query = match ($this->dbType) {
                'pgsql' => 'SELECT EXISTS(SELECT id FROM files WHERE hash = (:hash)), filename FROM files WHERE hash = (:hash) LIMIT 1',
                default => 'SELECT filename FROM files WHERE hash = (:hash) AND EXISTS (SELECT id FROM files WHERE hash = (:hash)) LIMIT 1'
            };
            $q = $this->DB->prepare($query);
            $q->bindValue(':hash', $hash);
            $q->execute();
            $result = $q->fetch();
            $q->closeCursor();
            if (!$result) {
                return [
                   'result' => false,
                ];
            } else {
                return [
                   'result' => true,
                   'name'   => $result['filename'],
                ];
            }
        }
        
        public function newIntoDB(array $FILE_INFO, array $fingerPrintInfo):void
        {
            $q = $this->DB->prepare(
               'INSERT INTO files (hash, originalname, filename, size, date, ip)' .
               'VALUES (:hash, :orig, :name, :size, :date, :ip)',
            );
            $q->bindValue(':hash', $FILE_INFO['XXH']);
            $q->bindValue(':orig', $FILE_INFO['NAME']);
            $q->bindValue(':name', $FILE_INFO['FILENAME']);
            $q->bindValue(':size', $FILE_INFO['SIZE'], PDO::PARAM_INT);
            $q->bindValue(':date', $fingerPrintInfo['timestamp']);
            $q->bindValue(':ip', $fingerPrintInfo['ip']);
            $q->execute();
            $q->closeCursor();
        }
        
        public function createRateLimit(array $fingerPrintInfo):void
        {
            $q = $this->DB->prepare(
               'INSERT INTO ratelimit (iphash, files, time)' .
               'VALUES (:iphash, :files, :time)',
            );
            $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
            $q->bindValue(':files', $fingerPrintInfo['files_amount']);
            $q->bindValue(':time', $fingerPrintInfo['timestamp']);
            $q->execute();
            $q->closeCursor();
        }
        
        public function updateRateLimit(int $fCount, bool $iStamp, array $fingerPrintInfo):void
        {
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
            $q->closeCursor();
        }
        
        public function compareTime(int $timestamp, int $seconds_d):bool
        {
            $diff = time() - $timestamp;
            if ($diff > $seconds_d) {
                return true;
            }
            return false;
        }
        
        public function checkRateLimit(array $fingerPrintInfo, int $rateTimeout, int $fileLimit):bool
        {
            $query = match ($this->dbType) {
                'pgsql' => 'SELECT EXISTS(SELECT id FROM ratelimit WHERE iphash = (:iphash)), id, iphash, files, time FROM ratelimit WHERE iphash = (:iphash) LIMIT 1',
                default => 'SELECT * FROM ratelimit WHERE iphash = (:iphash) AND EXISTS (SELECT id FROM ratelimit WHERE iphash = (:iphash)) LIMIT 1'
            };
            $q = $this->DB->prepare($query);
            $q->bindValue(':iphash', $fingerPrintInfo['ip_hash']);
            $q->execute();
            $result = $q->fetch();
            $q->closeCursor();
            //If there is no other match a record does not exist, create one.
            if (!$result) {
                $this->createRateLimit($fingerPrintInfo);
                return false;
            }
            // Apply rate-limit when file count reached and timeout not reached.
            if ($result['files'] === $fileLimit and !$this->compareTime($result['time'], $rateTimeout)) {
                return true;
            }
            // Update timestamp if timeout reached, reset file count and add the incoming file count.
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