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

class Response
{
    public mixed $type;

    public function __construct($response_type = "json")
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
                $this->error(400, 'Invalid response type. Valid options are: csv, html, json, text.');
                break;
        }
    }

    public function error($code, $desc): void
    {
        $response = match ($this->type) {
            'csv' => $this->csvError($desc),
            'html' => $this->htmlError($code, $desc),
            'json' => $this->jsonError($code, $desc),
            'text' => $this->textError($code, $desc),
        };
        http_response_code($code);
        echo $response;
    }

    private static function csvError($description): string
    {
        return '"error"' . "\r\n" . "\"$description\"" . "\r\n";
    }

    private static function htmlError($code, $description): string
    {
        return '<p>ERROR: (' . $code . ') ' . $description . '</p>';
    }

    private static function jsonError($code, $description): bool|string
    {
        return json_encode([
            'success' => false,
            'errorcode' => $code,
            'description' => $description,
        ], JSON_PRETTY_PRINT);
    }


    private static function textError($code, $description): string
    {
        return 'ERROR: (' . $code . ') ' . $description;
    }

    public function send($files): void
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

    private static function csvSuccess($files): string
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

    private static function htmlSuccess($files): string
    {
        $result = '';

        foreach ($files as $file) {
            $result .= '<a href="' . $file['url'] . '">' . $file['url'] . '</a><br>';
        }

        return $result;
    }

    private static function jsonSuccess($files): bool|string
    {
        return json_encode([
            'success' => true,
            'files' => $files,
        ], JSON_PRETTY_PRINT);
    }

    private static function textSuccess($files): string
    {
        $result = '';

        foreach ($files as $file) {
            $result .= $file['url'] . "\n";
        }

        return $result;
    }
}
