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
    
    class Response
    {
        public string $type;
        
        /**
         * Takes a string as an argument and sets the header to the appropriate content type
         *
         * @param $response_type string The type of response you want to return.
         *                       Valid options are: csv, html, json, text.
         */
        public function __construct(string $response_type)
        {
            switch ($response_type) {
                case 'csv':
                    header('Content-Type: text/csv; charset=UTF-8');
                    $this->type = $response_type;
                    break;
                case 'html':
                    header('Content-Type: text/html; charset=UTF-8');
                    $this->type = $response_type;
                    break;
                case 'json':
                    header('Content-Type: application/json; charset=UTF-8');
                    $this->type = $response_type;
                    break;
                case 'gyazo':
                    header('Content-Type: text/plain; charset=UTF-8');
                    $this->type = 'text';
                    break;
                case 'text':
                    header('Content-Type: text/plain; charset=UTF-8');
                    $this->type = $response_type;
                    break;
                default:
                    header('Content-Type: application/json; charset=UTF-8');
                    $this->type = 'json';
                    break;
            }
        }
        
        /**
         * Returns a string based on the type of response requested
         *
         * @param $code mixed The HTTP status code to return.
         * @param $desc string The description of the error.
         */
        public function error(int $code, string $desc):string
        {
            $response = match ($this->type) {
                'csv' => $this->csvError($desc),
                'html' => $this->htmlError($code, $desc),
                'json' => $this->jsonError($code, $desc),
                'text' => $this->textError($code, $desc),
            };
            http_response_code($code);
            echo $response;
            exit(1);
        }
        
        /* Returning a string that contains the error message. */
        private static function csvError(string $description):string
        {
            return '"error"' . "\r\n" . "\"$description\"" . "\r\n";
        }
        
        /**
         * Returns a string containing an HTML paragraph element with the error code and description
         *
         * @param $code        int|string The error code.
         * @param $description string The description of the error.
         *
         * @return string A string.
         */
        private static function htmlError(int|string $code, string $description):string
        {
            return '<p>ERROR: (' . $code . ') ' . $description . '</p>';
        }
        
        /**
         * Returns a JSON string with the error code and description
         *
         * @param $code        int|string The error code.
         * @param $description string The description of the error.
         *
         * @return bool|string A JSON string
         */
        private static function jsonError(int|string $code, string $description):bool|string
        {
            return json_encode([
               'success'     => false,
               'errorcode'   => $code,
               'description' => $description,
            ], JSON_PRETTY_PRINT);
        }
        
        /**
         * Returns a string that contains the error code and description
         *
         * @param $code        int|string The error code.
         * @param $description string The description of the error.
         *
         * @return string A string with the error code and description.
         */
        private static function textError(int|string $code, string $description):string
        {
            return 'ERROR: (' . $code . ') ' . $description;
        }
        
        /**
         * "If the type is csv, then call the csvSuccess function,
         * if the type is html, then call the htmlSuccess function, etc."
         *
         * The `match` keyword is a new feature in PHP 8. It's a lot like a switch statement, but it's more powerful
         *
         * @param $files array An array of file objects.
         */
        public function send(array $files):void
        {
            $response = match ($this->type) {
                'csv' => $this->csvSuccess($files),
                'html' => $this->htmlSuccess($files),
                'json' => $this->jsonSuccess($files),
                'text' => $this->textSuccess($files),
            };
            http_response_code(200); // "200 OK". Success.
            echo $response;
        }
        
        /**
         * Takes an array of files and returns a CSV string
         *
         * @param $files array An array of files that have been uploaded.
         *
         * @return string A string of the files in the array.
         */
        private static function csvSuccess(array $files):string
        {
            $result = '"name","url","hash","size"' . "\r\n";
            foreach ($files as $file) {
                $result .= '"' . $file['name'] . '"' . ',' .
                   '"' . $file['url'] . '"' . ',' .
                   '"' . $file['hash'] . '"' . ',' .
                   '"' . $file['size'] . '"' . "\r\n";
            }
            return $result;
        }
        
        /**
         * Takes an array of files and returns a string of HTML links
         *
         * @param $files array An array of files to be uploaded.
         *
         * @return string the result of the foreach loop.
         */
        private static function htmlSuccess(array $files):string
        {
            $result = '';
            foreach ($files as $file) {
                $result .= '<a href="' . $file['url'] . '">' . $file['url'] . '</a><br>';
            }
            return $result;
        }
        
        /**
         * Returns a JSON string that contains a success message and the files that were uploaded
         *
         * @param $files array The files to be uploaded.
         *
         * @return bool|string A JSON string
         */
        private static function jsonSuccess(array $files):bool|string
        {
            return json_encode([
               'success' => true,
               'files'   => $files,
            ], JSON_PRETTY_PRINT);
        }
        
        /**
         * Takes an array of files and returns a string of URLs
         *
         * @param $files array The files to be uploaded.
         *
         * @return string the url of the file.
         */
        private static function textSuccess(array $files):string
        {
            $result = '';
            foreach ($files as $file) {
                $result .= $file['url'] . "\n";
            }
            return $result;
        }
    }