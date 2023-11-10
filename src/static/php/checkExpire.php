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
require_once __DIR__ . '/vendor/autoload.php';

use Pomf\Uguu\Classes\expireChecker;

$check = new expireChecker();
$dbResult = $check->checkDB();
$check->cleanRateLimitDB();
if(empty($dbResult['ids'])){
    echo "No file(s) expired, nothing to do.";
} else {
    $check->deleteFromDB($dbResult['ids']);
    $check->deleteFiles($dbResult['filenames']);
}