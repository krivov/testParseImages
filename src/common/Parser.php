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

    protected $_url;

    protected $_urls = [];
    protected $_images;

    function __construct($url)
    {
        $this->_url = $url;
        $this->_html = file_get_contents($url);
    }

    public function isLoad() {
        return (boolean)$this->_html;
    }

    public function start() {
        $this->getAllImageUrl($this->_html);
    }


    protected function getAllImageUrl($htmlContent) {
        preg_match_all("/<img (.*)src=(\"|')(?<url>.*)(\"|')/U", $htmlContent, $matches);
        $imgArray = $matches['url'];
        $imgArray = array_keys(array_flip($imgArray));

        array_map(
            function($value) {
                $this->addImageUrl($value, $this->_url);
            },
            $imgArray
        );
    }

    protected function addImageUrl($imageUrl, $rootSite) {

        //remove all after sign '?'
        $t = strpos($imageUrl, '?');
        if (strpos($imageUrl, '?')) {
            $imageUrl = substr($imageUrl, 0, strpos($imageUrl, '?'));
        }

        //dont add link without extension
        if (!preg_match("#.(jpg|png|gif|jpeg)$#", $imageUrl)) {
            return;
        }

        $siteUrlParse = parse_url($rootSite);
        $siteUrlRoot = $siteUrlParse['scheme'] . "://" . $siteUrlParse['host'] . "/";

        if (isset($siteUrlParse['path']) && $siteUrlParse['path'] != '/') {
            $pathArray = explode('/', $siteUrlParse['path']);
            unset($pathArray[count($pathArray) - 1]);
            $siteUrlFolder = $siteUrlParse['scheme'] . "://" . $siteUrlParse['host'] . implode("/", $pathArray) . "/";
        } elseif (isset($siteUrlParse['path']) && $siteUrlParse['path'] == '/') {
            $siteUrlFolder = $rootSite;
        } else {
            $siteUrlFolder = $rootSite . "/";
        }

        if (preg_match("#http://|https://#", $imageUrl)) {
            $uploadUrl = $imageUrl;
        } else {
            if(substr($imageUrl,0,1) == '/') {
                $uploadUrl = $siteUrlRoot . substr($imageUrl,1);
            } else {
                $uploadUrl = $siteUrlFolder . $imageUrl;
            }
        }

        if (!in_array($imageUrl, $this->_urls)) {
            $this->_urls[] = $uploadUrl;
        }
    }
}