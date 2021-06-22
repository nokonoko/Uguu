<?php
/**
 * Handles POST uploads, generates filenames, moves files around and commits
 * uploaded metadata to database.
 */
require_once 'classes/Response.class.php';
require_once 'classes/UploadException.class.php';
require_once 'classes/UploadedFile.class.php';
require_once 'includes/database.inc.php';

/**
 * Generates a random name for the file, retrying until we get an unused one.
 *
 * @param UploadedFile $file
 *
 * @return string
 */
function generateName($file)
{
    global $db;
    global $doubledots;

    // We start at N retries, and --N until we give up
    $tries = UGUU_FILES_RETRIES;
    $length = UGUU_FILES_LENGTH;

    //Get EXT
    $ext = pathinfo($file->name, PATHINFO_EXTENSION);

    //Get MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $type_mime = finfo_file($finfo, $file->tempfile);
    finfo_close($finfo);

    // Check if extension is a double-dot extension and, if true, override $ext
    $revname = strrev($file->name);
    foreach ($doubledots as $ddot) {
        if (stripos($revname, $ddot) === 0) {
            $ext = strrev($ddot);
        }
    }

    do {
        // Iterate until we reach the maximum number of retries
        if ($tries-- === 0) {
            http_response_code(500);
            throw new Exception(
                'Gave up trying to find an unused name',
                500
            ); // HTTP status code "500 Internal Server Error"
        }

        $chars = ID_CHARSET;
        $name = '';
        for ($i = 0; $i < $length; ++$i) {
            $name .= $chars[mt_rand(0, strlen($chars))];
        }

        // Add the extension to the file name
        if (isset($ext) && $ext !== '') {
            $name .= '.'.$ext;
        }

        //Check if MIME is blacklisted
        if (in_array($type_mime, unserialize(CONFIG_BLOCKED_MIME))) {
            http_response_code(415);
            throw new UploadException(UPLOAD_ERR_EXTENSION);
            exit(0);
        }
        //Check if EXT is blacklisted
        if (in_array($ext, unserialize(CONFIG_BLOCKED_EXTENSIONS))) {
            http_response_code(415);
            throw new UploadException(UPLOAD_ERR_EXTENSION);
            exit(0);
        }

        // Check if a file with the same name does already exist in the database
        $q = $db->prepare('SELECT COUNT(filename) FROM files WHERE filename = (:name)');
        $q->bindValue(':name', $name, PDO::PARAM_STR);
        $q->execute();
        $result = $q->fetchColumn();
        // If it does, generate a new name
    } while ($result > 0);

    return $name;
}

/**
 * Handles the uploading and db entry for a file.
 *
 * @param UploadedFile $file
 *
 * @return array
 */
function uploadFile($file)
{
    global $db;
    global $FILTER_MODE;
    global $FILTER_MIME;

    // Handle file errors
    if ($file->error) {
        throw new UploadException($file->error);
    }

    //fixes a bug
    $lol = $file->getSha1();

    // Check if a file with the same hash and size (a file which is the same)
    // does already exist in the database; if it does, return the proper link
    // and data. PHP deletes the temporary file just uploaded automatically.
    if(ANTI_DUPE == 'true'){
    $q = $db->prepare('SELECT filename, COUNT(*) AS count FROM files WHERE hash = (:hash) AND size = (:size)');
    $q->bindValue(':hash', $file->getSha1(), PDO::PARAM_STR);
    $q->bindValue(':size', $file->size, PDO::PARAM_INT);
    $q->execute();
    $result = $q->fetch();
    if ($result['count'] > 0) {
        return [
            'hash' => $file->getSha1(),
            'name' => $file->name,
            'url' => UGUU_URL.rawurlencode($result['filename']),
            'size' => $file->size,
        ];
    }
}

    // Get IP
    $ip = $_SERVER['REMOTE_ADDR'];

    // Generate a name for the file
    $newname = generateName($file);

    // Store the file's full file path in memory
    $uploadFile = UGUU_FILES_ROOT.$newname;

    // Attempt to move it to the static directory
    if (!move_uploaded_file($file->tempfile, $uploadFile)) {
        http_response_code(500);
        throw new Exception(
            'Failed to move file to destination',
            500
        ); // HTTP status code "500 Internal Server Error"
    }

    // Need to change permissions for the new file to make it world readable
    if (!chmod($uploadFile, 0644)) {
        http_response_code(500);
        throw new Exception(
            'Failed to change file permissions',
            500
        ); // HTTP status code "500 Internal Server Error"
    }

    // Add it to the database
        if(LOG_IP == 'true'){
        $q = $db->prepare('INSERT INTO files (hash, originalname, filename, size, date, ip) VALUES (:hash, :orig, :name, :size, :date, :ip)');
        } else {
        $ip = '0';
        $q = $db->prepare('INSERT INTO files (hash, originalname, filename, size, date, ip) VALUES (:hash, :orig, :name, :size, :date, :ip)');
        }

    // Common parameters binding
    $q->bindValue(':hash', $file->getSha1(), PDO::PARAM_STR);
    $q->bindValue(':orig', strip_tags($file->name), PDO::PARAM_STR);
    $q->bindValue(':name', $newname, PDO::PARAM_STR);
    $q->bindValue(':size', $file->size, PDO::PARAM_INT);
    $q->bindValue(':date', time(), PDO::PARAM_STR);
    $q->bindValue(':ip', $ip, PDO::PARAM_STR);
    $q->execute();

    return [
        'hash' => $file->getSha1(),
        'name' => $file->name,
        'url' => UGUU_URL.rawurlencode($newname),
        'size' => $file->size,
    ];
}

/**
 * Reorder files array by file.
 *
 * @return array
 */
function diverseArray($files)
{
    $result = [];

    foreach ($files as $key1 => $value1) {
        foreach ($value1 as $key2 => $value2) {
            $result[$key2][$key1] = $value2;
        }
    }

    return $result;
}

/**
 * Reorganize the $_FILES array into something saner.
 *
 * @return array
 */
function refiles($files)
{
    $result = [];
    $files = diverseArray($files);

    foreach ($files as $file) {
        $f = new UploadedFile();
        $f->name = $file['name'];
        $f->mime = $file['type'];
        $f->size = $file['size'];
        $f->tempfile = $file['tmp_name'];
        $f->error = $file['error'];
        $result[] = $f;
    }

    return $result;
}

$type = isset($_GET['output']) ? $_GET['output'] : 'json';
$response = new Response($type);

if (isset($_FILES['files'])) {
    $uploads = refiles($_FILES['files']);

    try {
        foreach ($uploads as $upload) {
            $res[] = uploadFile($upload);
        }
        $response->send($res);
    } catch (Exception $e) {
        $response->error($e->getCode(), $e->getMessage());
    }
} else {
    $response->error(400, 'No input file(s)');
}
