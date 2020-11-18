<?php
/**
 * The Response class is a do-it-all for getting responses out in different
 * formats.
 *
 * @todo Create sub-classes to split and extend this god object.
 */
class Response
{
    /**
     * Indicates response type used for routing.
     *
     * Valid strings are 'csv', 'html', 'json' and 'text'.
     *
     * @var string $type Response type
     */
    private $type;

    /**
     * Indicates requested response type.
     *
     * Valid strings are 'csv', 'html', 'json', 'gyazo' and 'text'.
     *
     * @param string|null $response_type Response type
     */
    public function __construct($response_type = null)
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

    /**
     * Routes error messages depending on response type.
     *
     * @param int $code HTTP status code number.
     * @param int $desc Descriptive error message.
     * @return void
     */
    public function error($code, $desc)
    {
        $response = null;

        switch ($this->type) {
            case 'csv':
                $response = $this->csvError($desc);
                break;
            case 'html':
                $response = $this->htmlError($code, $desc);
                break;
            case 'json':
                $response = $this->jsonError($code, $desc);
                break;
            case 'text':
                $response = $this->textError($code, $desc);
                break;
        }

        http_response_code(500); // "500 Internal Server Error"
        echo $response;
    }

    /**
     * Routes success messages depending on response type.
     *
     * @param mixed[] $files
     * @return void
     */
    public function send($files)
    {
        $response = null;

        switch ($this->type) {
            case 'csv':
                $response = $this->csvSuccess($files);
                break;
            case 'html':
                $response = $this->htmlSuccess($files);
                break;
            case 'json':
                $response = $this->jsonSuccess($files);
                break;
            case 'text':
                $response = $this->textSuccess($files);
                break;
        }

        http_response_code(200); // "200 OK". Success.
        echo $response;
    }

    /**
     * Indicates with CSV body the request was invalid.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param int $description Descriptive error message.
     * @return string Error message in CSV format.
     */
    private static function csvError($description)
    {
        return '"error"'."\r\n"."\"$description\""."\r\n";
    }

    /**
     * Indicates with CSV body the request was successful.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param mixed[] $files
     * @return string Success message in CSV format.
     */
    private static function csvSuccess($files)
    {
        $result = '"name","url","hash","size"'."\r\n";
        foreach ($files as $file) {
            $result .= '"'.$file['name'].'"'.','.
                       '"'.$file['url'].'"'.','.
                       '"'.$file['hash'].'"'.','.
                       '"'.$file['size'].'"'."\r\n";
        }

        return $result;
    }

    /**
     * Indicates with HTML body the request was invalid.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param int $code HTTP status code number.
     * @param int $description Descriptive error message.
     * @return string Error message in HTML format.
     */
    private static function htmlError($code, $description)
    {
        return '<p>ERROR: ('.$code.') '.$description.'</p>';
    }

    /**
     * Indicates with HTML body the request was successful.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param mixed[] $files
     * @return string Success message in HTML format.
     */
    private static function htmlSuccess($files)
    {
        $result = '';

        foreach ($files as $file) {
            $result .=  '<a href="'.$file['url'].'">'.$file['url'].'</a><br>';
        }

        return $result;
    }

    /**
     * Indicates with JSON body the request was invalid.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param int $code HTTP status code number.
     * @param int $description Descriptive error message.
     * @return string Error message in pretty-printed JSON format.
     */
    private static function jsonError($code, $description)
    {
        return json_encode(array(
            'success' => false,
            'errorcode' => $code,
            'description' => $description,
        ), JSON_PRETTY_PRINT);
    }

    /**
     * Indicates with JSON body the request was successful.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param mixed[] $files
     * @return string Success message in pretty-printed JSON format.
     */
    private static function jsonSuccess($files)
    {
        return json_encode(array(
            'success' => true,
            'files' => $files,
        ), JSON_PRETTY_PRINT);
    }

    /**
     * Indicates with plain text body the request was invalid.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param int $code HTTP status code number.
     * @param int $description Descriptive error message.
     * @return string Error message in plain text format.
     */
    private static function textError($code, $description)
    {
        return 'ERROR: ('.$code.') '.$description;
    }

    /**
     * Indicates with plain text body the request was successful.
     *
     * @deprecated 2.1.0 Will be renamed to camelCase format.
     * @param mixed[] $files
     * @return string Success message in plain text format.
     */
    private static function textSuccess($files)
    {
        $result = '';

        foreach ($files as $file) {
            $result .= $file['url']."\n";
        }

        return $result;
    }
}
