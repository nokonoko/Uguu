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
    
    class Connector extends Database
    {
        public PDO $DB;
        public array $CONFIG;
        
        /**
         * Reads the config.json file and populates the CONFIG property with the settings
         *
         * @throws \Exception
         */
        public function __construct()
        {
            if (!file_exists(__DIR__ . '/../config.json')) {
                throw new Exception('Cant read settings file.', 500);
            }
            try {
                $this->CONFIG = json_decode(
                   file_get_contents(__DIR__ . '/../config.json'),
                   true,
                );
                $this->assemble();
            }
            catch (Exception $e) {
                throw new Exception($e->getMessage(), 500);
            }
        }
        
        /**
         * > Tries to connect to the database
         *
         * @throws \Exception
         */
        public function assemble()
        {
            try {
                $this->DB = new PDO(
                   $this->CONFIG['DB_MODE'] . ':' . $this->CONFIG['DB_PATH'],
                   $this->CONFIG['DB_USER'],
                   $this->CONFIG['DB_PASS']
                );
            }
            catch (Exception) {
                throw new Exception('Cant connect to DB.', 500);
            }
        }
    }
