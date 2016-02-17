<?php
/**
 * Created by PhpStorm.
 * User: krivov
 * Date: 16.02.16
 * Time: 20:41
 */

include "common/Parser.php";
include "common/ParserPluginAbstract.php";
include "common/Image.php";

define("TMP_FOLDER_PATH", realpath(__DIR__ . '/../tmp/'));

/**
 * autoload all plugins from plugins folder
 */
function autoloadPlugins() {

    $pluginFolder = realpath(__DIR__ . '/plugins/');
    $dir = opendir($pluginFolder);

    $pluginsArray = [];

    while ($fileName = readdir($dir))
    {
        if (
            ($fileName != ".") &&
            ($fileName != "..") &&
            is_file($pluginFolder . '/' . $fileName) &&
            is_readable($pluginFolder . '/' . $fileName)
        ) {
            include_once $pluginFolder . '/' . $fileName;

            $className = substr($fileName, 0, strpos($fileName, '.'));

            if (class_exists($className)) {
                $pluginsArray[] = new $className();
            }
        }
    }
    closedir($dir);

    return $pluginsArray;
}