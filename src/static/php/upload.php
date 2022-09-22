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

error_reporting(0);

require_once __DIR__ . '/../vendor/autoload.php';

use Pomf\Uguu\UploadGateway;

$Upload = new UploadGateway();
try {
    $Upload->handleFile($_GET['output'], $_FILES['files']);
} catch (Exception $e) {
    throw new Exception($e->getMessage(), 500);
}
