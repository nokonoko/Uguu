<?php

class UploadedFile
{
    /* Public attributes */
    public $name;
    public $mime;
    public $size;
    public $tempfile;
    public $error;

    /**
     * SHA-1 checksum
     *
     * @var string 40 digit hexadecimal hash (160 bits)
     */
    private $sha1;

    /**
     * Generates the SHA-1 or returns the cached SHA-1 hash for the file.
     *
     * @return string|false $sha1
     */
    public function getSha1()
    {
        if (!$this->sha1) {
            $this->sha1 = sha1_file($this->tempfile);
        }

        return $this->sha1;
    }
}
