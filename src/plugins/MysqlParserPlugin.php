<?php

/**
 * Parser plugin - save all images to mysql
 *
 * User: krivov
 * Date: 17.02.16
 * Time: 23:26
 */
class MysqlParserPlugin extends ParserPluginAbstract
{
    protected static $_connection = NULL;

    const DB_HOST = "localhost";
    const DB_NAME = "test_parse_image";
    const DB_USER = "root";
    const DB_PASSWORD = "root";

    public function job(Image $image)
    {
        if (!is_file($image->tempFile)) {
            return false;
        }

        $imageContent = file_get_contents($image->tempFile);

        if (!$imageContent) {
            return false;
        }

        $query = "INSERT INTO images (image, source_url) VALUES ('" . addslashes($imageContent)."', '" . mysqli_real_escape_string(MysqlParserPlugin::$_connection, $image->url) . "')";
        $res = mysqli_query(MysqlParserPlugin::$_connection, $query);

        return (boolean)$res;
    }

    /**
     * MysqlParserPlugin constructor.
     */
    public function __construct()
    {
        self::getConnection();
    }

    /**
     * Get connection to database
     *
     * @return resource
     * @throws Exception
     */
    public static function getConnection()
    {
        if (self::$_connection === null) {
            $connection = @mysqli_connect(self::DB_HOST, self::DB_USER, self::DB_PASSWORD);
            if (!$connection) {
                throw new Exception('Unable to connect to MySQL.');
            }
            $db = mysqli_select_db($connection, self::DB_NAME);
            if (!$db) {
                throw new Exception('Unable to select MySQL database.');
            }
            mysqli_set_charset($connection, "utf8");

            self::$_connection = $connection;
        }
        return self::$_connection;
    }
}