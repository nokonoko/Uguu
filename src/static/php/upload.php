<?php
    
    /**
     * Uguu
     *
     * @copyright Copyright (c) 2022-2023 Go Johansson (nokonoko) <neku@pomf.se>
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
    require_once __DIR__ . '/../vendor/autoload.php';
    
    use Pomf\Uguu\Classes\Upload;
    use Pomf\Uguu\Classes\Response;
    
    function handleFiles(string $outputFormat, array $files):void
    {
        $upload = new Upload($outputFormat);
        $files = $upload->reFiles($files);
        $fCount = count($files);
        $upload->fingerPrint($fCount);
        $res = [];
        $i = 0;
        while ($i < $fCount) {
            $res[] = $upload->uploadFile();
            $i++;
        }
        if (!empty($res)) {
            $upload->send($res);
        }
    }
    $resType = (isset($_GET['output']) and !empty($_GET['output'])) ? strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['output'])) : 'json';
    $response = new Response($resType);
    if (!isset($_FILES['files']) or empty($_FILES['files'])) {
        $response->error(400, 'No input file(s)');
    }
    handleFiles($resType, $_FILES['files']);