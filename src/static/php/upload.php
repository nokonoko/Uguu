<?php
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
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
    require_once __DIR__ . '/../vendor/autoload.php';
    
    use Pomf\Uguu\Classes\Upload;
    use Pomf\Uguu\Classes\Response;

    /**
     * It takes a string and an array as arguments, creates a new Upload object,
     * calls the reFiles method on the Upload object, calls the fingerPrint method on
     * the Upload object, calls the uploadFile method on the Upload object,
     * calls the send method on the Upload object, and calls the error method on the
     * Upload object
     *
     * @param $outputFormat string The format of the output, json or xml
     * @param $files        array The file to be uploaded, which is an array.
     *
     * @throws \Exception
     */
function handleFile(string $outputFormat, array $files): void
{
    $upload = new Upload($outputFormat);
    $files = $upload->reFiles($files);
    try {
        $upload->fingerPrint(count($files));
        $res = [];
        foreach ($files as $ignored) {
            $res[] = $upload->uploadFile();
        }
        if (!empty($res)) {
            $upload->send($res);
        }
    } catch (Exception $e) {
        $upload->error($e->getCode(), $e->getMessage());
    }
}
    
    $response = new Response('json');

if (!isset($_FILES['files']) or empty($_FILES['files'])) {
    $response->error(400, 'No input file(s)');
}
if (isset($_GET['output']) and !empty($_GET['output'])) {
    $resType = strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['output']));
} else {
    $resType = 'json';
}

try {
    handleFile($resType, $_FILES['files']);
} catch (Exception $e) {
    $response->error($e->getCode(), $e->getMessage());
}
