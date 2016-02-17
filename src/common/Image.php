<?php

/**
 * Image model
 *
 * User: krivov
 * Date: 17.02.16
 * Time: 21:44
 */
class Image
{
    /**
     * Image source url
     *
     * @var string
     */
    public $url;

    /**
     * Image path to temporary file
     *
     * @var
     */
    public $tempFile;

    function __construct($url)
    {
        $this->url = $url;

        $urlHash = md5($url);
        $extension = pathinfo(parse_url($url)['path'], PATHINFO_EXTENSION);
        $filePath = TMP_FOLDER_PATH . "/" . $urlHash . "." . $extension;

        $this->tempFile = $filePath;

        if (!file_exists($filePath)) {
            $newFileContent = @file_get_contents($url);
            if ($newFileContent) {
                file_put_contents($filePath, $newFileContent);
            } else {
                echo "Error download: " . $url . PHP_EOL;
            }
        }
    }
}