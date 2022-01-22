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

require_once 'includes/Upload.class.php';

use Core\Response as Response;

if (isset($_FILES['files'])) {
    $uploads = (new Upload())->reFiles($_FILES['files']);

    foreach ($uploads as $upload) {
        $res[] = (new Upload())->uploadFile($upload);
    }

    if (isset($res)) {
        (new Response())->returnSuccess($res);
    } else {
        (new Response())->returnError(400, 'No input file(s)', 'N/A');
    }
}