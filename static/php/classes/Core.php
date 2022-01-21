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

    /**
     * @property mixed $DB_CONN
     */
    class Settings
    {

        public $DB_MODE;
        public $DB_PATH;
        public $DB_USER;
        public $DB_PASS;

        public $LOG_IP;
        public $ANTI_DUPE;
        public $BLACKLIST_DB;
        public $FILTER_MODE;

        public $FILES_ROOT;
        public $FILES_RETRIES;

        public $SSL;
        public $URL;

        public $NAME_LENGTH;
        public $ID_CHARSET;
        public $BLOCKED_EXTENSIONS;
        public $BLOCKED_MIME;
        public $DOUBLE_DOTS;

        public function __constructSettings()
        {
            $settings_array = json_decode(file_get_contents('/Users/go.johansson/PERSONAL_REPOS/Uguu/dist.json'), true);
            $this->DB_MODE = $settings_array['DB_MODE'];
            $this->DB_PATH = $settings_array['DB_PATH'];
            $this->DB_USER = $settings_array['DB_USER'];
            $this->DB_PASS = $settings_array['DB_PASS'];
            $this->LOG_IP = $settings_array['LOG_IP'];
            $this->ANTI_DUPE = $settings_array['ANTI_DUPE'];
            $this->BLACKLIST_DB = $settings_array['BLACKLIST_DB'];
            $this->FILTER_MODE = $settings_array['FILTER_MODE'];
            $this->FILES_ROOT = $settings_array['FILES_ROOT'];
            $this->FILES_RETRIES = $settings_array['FILES_RETRIES'];
            $this->SSL = $settings_array['SSL'];
            $this->URL = $settings_array['URL'];
            $this->NAME_LENGTH = $settings_array['NAME_LENGTH'];
            $this->ID_CHARSET = $settings_array['ID_CHARSET'];
            $this->BLOCKED_EXTENSIONS = $settings_array['BLOCKED_EXTENSIONS'];
            $this->BLOCKED_MIME = $settings_array['BLOCKED_MIME'];
            $this->DOUBLE_DOTS = $settings_array['DOUBLE_DOTS'];
        }
    }

    class Database extends Settings
    {
        public $DB;

        public function __constructDB()
        {
            $this->DB = new PDO($this->DB_MODE.':'.$this->DB_PATH, $this->DB_USER, $this->DB_PASS);
        }
    }
}