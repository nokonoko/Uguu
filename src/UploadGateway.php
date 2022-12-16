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
    
    namespace Pomf\Uguu;
    
    use Exception;
    use Pomf\Uguu\Classes\Response;
    
    class UploadGateway extends Classes\Upload
    {
        /**
         * It handles the file uploads.
         *
         * @param $output mixed The output format of the response.
         * @param $files  mixed The name of the file input field.
         *
         * @throws \Exception
         */
        public function handleFile(mixed $output, mixed $files)
        {
            $type = 'json' ?? $output;
            $response = (new Response($type));
            if (!empty($_FILES['files'])) {
                
                $files = $this->reFiles($files);
                try {
                    $this->fingerPrint(count($files));
                    $res = [];
                    foreach ($files as $ignored) {
                        $res[] = $this->uploadFile();
                    }
                    if (!empty($res)) {
                        $response->send($res);
                    }
                }
                catch (Exception $e) {
                    $response->error($e->getCode(), $e->getMessage());
                }
                
            } else {
                $response->error(400, 'No input file(s)');
            }
        }
    }