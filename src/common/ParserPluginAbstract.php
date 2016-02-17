<?php

/**
 * Abstract class for parser plugins
 *
 * User: krivov
 * Date: 17.02.16
 * Time: 23:27
 */
abstract class ParserPluginAbstract
{
    /**
     * Something job doing for every image
     *
     * @param Image $image
     *
     * @return mixed
     */
    abstract public function job(Image $image);
}