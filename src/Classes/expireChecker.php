<?php

/*
 * Uguu
 *
 * @copyright Copyright (c) 2022-2025 Go Johansson (nokonoko) <neku@pomf.se>
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

class expireChecker
{
    public PDO $DB;
    public string $dbType;
    public array $CONFIG;
    public string $timeUnit;
    /**
     * @var \Pomf\Uguu\Classes\Response
     */
    private Response $response;
    public function checkDB(): bool|array
    {
        if (is_int($this->CONFIG['expireTime'])) {
            $this->timeUnit = strtoupper($this->CONFIG['expireTimeUnit']);
            if (!in_array($this->timeUnit, ['SECONDS', 'MINUTES', 'HOURS', 'DAYS', 'WEEKS', 'MONTHS', 'YEARS'])) {
                $this->timeUnit = "HOURS";
            }
            $query = match ($this->dbType) {
                'pgsql' => 'SELECT id, filename FROM files WHERE date < EXTRACT(epoch from NOW() - INTERVAL \'' . $this->CONFIG['expireTime'] . ' ' . $this->timeUnit . '\')',
                'mysql' => 'SELECT id, filename FROM files WHERE date <= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ' . (int)$this->CONFIG['expireTime'] . ' ' . rtrim($this->timeUnit, 'S') . '))',
                default => 'SELECT id, filename FROM files WHERE date <= strftime(\'%s\', datetime(\'now\', \'-' . $this->CONFIG['expireTime'] . ' ' . $this->timeUnit . '\'));'
            };
            $q = $this->DB->prepare($query);
            $q->execute();
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            $q->closeCursor();
            $returnArray = [
               'ids'       => [],
               'filenames' => [],
            ];
            foreach ($result as $array) {
                $returnArray['ids'][] = $array['id'];
                $returnArray['filenames'][] = $array['filename'];
            }
            return $returnArray;
        } else {
            return false;
        }
    }

    public function cleanRateLimitDB(): void
    {
        $query = match ($this->dbType) {
            'pgsql' => 'DELETE FROM ratelimit WHERE time < EXTRACT(epoch from NOW() - INTERVAL \'24 HOURS\')',
            'mysql' => 'DELETE FROM ratelimit WHERE time <= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))',
            default => 'DELETE FROM ratelimit WHERE time <= strftime(\'%s\', datetime(\'now\', \'-24 HOURS\'));'
        };
        $q = $this->DB->prepare($query);
        $q->execute();
        $q->closeCursor();
    }

    public function deleteFiles(array $filenames): void
    {
        foreach ($filenames as $filename) {
            unlink($this->CONFIG['FILES_ROOT'] . $filename);
        }
    }

    public function deleteFromDB(array $ids): void
    {
        foreach ($ids as $id) {
            $query = match ($this->dbType) {
                'pgsql' => 'DELETE FROM files WHERE id = (:id)',
                default => 'DELETE FROM files WHERE id = (:id)'
            };
            $q = $this->DB->prepare($query);
            $q->bindValue(':id', $id);
            $q->execute();
            $q->closeCursor();
        }
    }

    /**
     * Loads configuration either from APCu cache or JSON file
     */
    private function loadConfig(): array
    {
        $apcuLoaded = extension_loaded('apcu') && apcu_enabled();
        if ($apcuLoaded && ($config = apcu_fetch('UC', $success)) && $success) {
            return $config;
        }
        if (!is_readable($configFile = __DIR__ . '/../config.json')) {
            $this->response->error(500, 'Cant read settings file.');
        }
        $config = json_decode(file_get_contents($configFile), true)
           ?: $this->response->error(500, 'Invalid JSON in config file.');
        $apcuLoaded && apcu_store('UC', $config);
        return $config;
    }

    /**
     * Initialize configuration, set up error handling, and prepare the environment.
     *
     * @return void
     */
    public function __construct()
    {
        $this->response = new Response('json');
        $this->CONFIG = $this->loadConfig();
        ini_set('display_errors', 0);
        $this->dbType = $this->CONFIG['DB_MODE'];
        $this->DB = new PDO(
            $this->CONFIG['DB_MODE'] . ':' . $this->CONFIG['DB_PATH'],
            $this->CONFIG['DB_USER'],
            $this->CONFIG['DB_PASS'],
        );
    }
}
