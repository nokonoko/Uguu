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

namespace Pomf\Uguu\Benchmarks;

use Pomf\Uguu\Classes\Upload;
use Pomf\Uguu\Classes\Response;

class UguuBench
{
    public function handleFiles(string $outputFormat, array $files): void
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

    public function handleUp()
    {
        //$tmp = tempnam(__DIR__ . '/tmp/', 'benchmarkUguu');
        //file_put_contents($tmp, file_get_contents(__DIR__ . '/file.jpg'));
        // Mock the $_SERVER array
        $_SERVER = [
           'HTTP_USER_AGENT' => 'curl/8.4.0',
           'REMOTE_ADDR'     => '1.2.3.4'
        ];
        // Mock the $_FILES array
        $_FILES = [
           'files' => [
              'name'      => [
                 0 => 'file.jpg',
              ],
              'full_path' => [
                 0 => 'file.jpg',
              ],
              'type'      => [
                 0 => 'image/jpeg',
              ],
              'tmp_name'  => [
                 0 => __DIR__ . '/file.jpg',
              ],
              'error'     => [
                 0 => 0,
              ],
              'size'      => [
                 0 => 12345,
              ],
           ],
        ];
        $_GET['output'] = 'benchmark';
        $resType = (isset($_GET['output']) and !empty($_GET['output'])) ? strtolower(preg_replace('/[^a-zA-Z]/', '', $_GET['output'])) : 'json';
        $response = new Response($resType);
        if (!isset($_FILES['files']) or empty($_FILES['files'])) {
            $response->error(400, 'No input file(s)');
        }
        $this->handleFiles($resType, $_FILES['files']);
    }

    /**
     * @Revs(500)
     * @Iterations(15)
     */
    public function benchTest()
    {
        $this->handleUp();
    }
}
