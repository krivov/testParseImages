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
    /** @var  string */
    protected $_html;

    /** @var  string */
    protected $_url;

    /** @var array  */
    protected $_urls = [];

    /** @var  array */
    protected $_images;

    /** @var  ParserPluginAbstract[] */
    protected $_plugins;

    /**
     * Parser constructor.
     *
     * @param $url
     */
    function __construct($url)
    {
        $this->_url = $url;
        $this->_html = @file_get_contents($url);
    }

    /**
     * Check is load main html
     *
     * @return bool
     */
    public function isLoad() {
        return (boolean)$this->_html;
    }

    /**
     * Start parse process
     */
    public function start() {
        $this->getAllImageUrl($this->_html);
        $this->getAllCss($this->_html, $this->_url);
        $this->downloadAllImg();
    }

    /**
     * Get all image url and add them to $this->_urls array
     *
     * @param $htmlContent
     */
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

    /**
     * Add image url to $this->_urls array
     *
     * @param $imageUrl
     * @param $rootSite
     */
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

        $uploadUrl = $this->prepareUrl($imageUrl, $rootSite);

        if (!in_array($uploadUrl, $this->_urls)) {
            $this->_urls[] = $uploadUrl;
        }
    }

    protected function prepareUrl($imageUrl, $rootSite) {
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

        return $uploadUrl;
    }

    /**
     * Get all css from html or css and parse it
     *
     * @param $htmlContent
     * @param $rootUrl
     */
    protected function getAllCss($htmlContent, $rootUrl) {

        $imagesFromCss = [];

        preg_match_all("/<link (.*)href=(\"|')(?<url>.*)(\"|')/U", $htmlContent, $matches);
        $linkArray = $matches['url'];
        if(!empty($linkArray)) {
            array_map(
                function($url) use($rootUrl) {
                    $this->parseCss($url, $rootUrl);
                },
                $linkArray
            );
        }

        preg_match_all("#url\((.*)(\"|')(?<url>.*)(\"|')(.*)\)#U", $htmlContent, $matches);
        $urlArray = $matches['url'];
        if(!empty($urlArray)) {
            array_map(
                function($url) use($rootUrl) {
                    $this->parseCss($url, $rootUrl);
                    $this->addImageUrl($url, $rootUrl);
                },
                $urlArray
            );
        }
    }

    /**
     * Download css and parse all images
     *
     * @param $urlCss
     * @param $rootUrl
     */
    protected function parseCss($urlCss, $rootUrl) {
        //dont parse link without css extension
        if (!preg_match("#.(css)$#", $urlCss)) {
            return;
        }

        $urlToUpload = $this->prepareUrl($urlCss, $rootUrl);
        $cssHtml = @file_get_contents($urlToUpload);

        if ($cssHtml) {
            $this->getAllCss($cssHtml, $urlToUpload);
        } else {
            echo "Error download: " . $urlToUpload . PHP_EOL;
        }
    }

    /**
     * Download all parsed images
     */
    protected function downloadAllImg() {
        foreach($this->_urls as $url) {
            $newImage = new Image($url);

            $this->_images[] = $newImage;
            $this->runAllPlugins($newImage);
        }
    }

    /**
     * Add plugins
     *
     * @param $plugins
     */
    public function addPlugins($plugins) {
        $this->_plugins = $plugins;
    }

    /**
     * Run all plugins for one image
     *
     * @param Image $image
     */
    protected function runAllPlugins(Image $image) {
        if ($this->_plugins) {
            foreach($this->_plugins as $plugin) {
                if ($plugin instanceof ParserPluginAbstract) {
                    $plugin->job($image);
                }
            }
        }
    }
}