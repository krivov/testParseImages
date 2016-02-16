<?php

/**
 * Main class for parse html
 *
 * User: krivov
 * Date: 16.02.16
 * Time: 21:17
 */
class Parser
{
    protected $_html;

    function __construct($url)
    {
        $this->_html = file_get_contents($url);
    }

    public function isLoad() {
        return (boolean)$this->_html;
    }
}