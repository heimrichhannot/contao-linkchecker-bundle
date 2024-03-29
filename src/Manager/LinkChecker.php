<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Manager;

use Contao\FrontendTemplate;
use Contao\Validator;
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;

class LinkChecker
{
    const STATUS_MAILTO = 'mailto';
    const STATUS_INVALID = 'invalid';
    const STATUS_TIMEOUT = '408';

    const CLASS_DEFAULT = 'lc-default';
    const CLASS_INFO = 'lc-info';
    const CLASS_SUCCESS = 'lc-success';
    const CLASS_ERROR = 'lc-error';

    private CurlRequestUtil $curlRequestUtil;

    public function __construct(CurlRequestUtil $curlRequestUtil)
    {
        $this->curlRequestUtil = $curlRequestUtil;
    }

    /**
     * Test a given link, or all links that has been added with add().
     *
     * @param string|array $varLinks A single url or an array with multiple urls as value
     *
     * @return array|bool|mixed
     */
    public function test($varLinks)
    {
        if (!\is_array($varLinks)) {
            return $this->testOne($varLinks);
        }

        return $this->testAll($varLinks);
    }

    /**
     * Test a single url.
     *
     * @param string $url The url
     *
     * @return string The translated status code, or false if the link was not tested
     */
    protected function testOne(string $url): string
    {
        if (str_starts_with($url, 'mailto:')) {
            return $this->getResult(static::STATUS_MAILTO);
        }

        if (!Validator::isUrl($url)) {
            return $this->getResult(static::STATUS_INVALID);
        }

        list($headers, $body) = $this->curlRequestUtil->request($url, [], true);

        if (\is_array($headers)) {
            return $this->getResult($headers['http_code']);
        }

        return $this->getResult(static::STATUS_TIMEOUT);
    }

    /**
     * Test a list of links.
     *
     * @param array $arrLinks Array with multiple urls as value
     *
     * @return array The  list of tested links with translated status code, or false if the link was not tested
     */
    protected function testAll(array $arrLinks): array
    {
        $arrResults = [];

        foreach ($arrLinks as $strKey => $strUrl) {
            $arrResults[$strUrl] = $this->testOne($strUrl);
            unset($arrLinks);
        }

        return $arrResults;
    }

    /**
     * Get the styled result.
     */
    protected function getResult(string $result): string
    {
        $objTemplate = new FrontendTemplate('linkchecker_result_default');

        $text = $result;

        if (isset($GLOBALS['TL_LANG']['linkChecker']['statusCodes'][$result])) {
            $text = $GLOBALS['TL_LANG']['linkChecker']['statusCodes'][$result];
        } elseif (CurlRequestUtil::HTTP_STATUS_CODE_MESSAGES[$result]) {
            $text = CurlRequestUtil::HTTP_STATUS_CODE_MESSAGES[$result].' (Statuscode: '.$result.')';
        }

        $objTemplate->text = $text;

        $objTemplate->status = $this->getStatusClass($result);

        return $objTemplate->parse();
    }

    /**
     * Get the status class for a given result.
     */
    protected function getStatusClass(string $statusCode): string
    {
        $intStart = null;

        if (\strlen($statusCode) > 0) {
            $intStart = substr($statusCode, 0, 1);
        }

        switch ($intStart) {
            //1xx Informational
            //3xx Redirection
            case '1':
            case '3':
                return static::CLASS_INFO;
            //2xx Success
            case '2':
                return static::CLASS_SUCCESS;

            // 4xx Client Error
            // 5xx Server Error
            case '4':
            case '5':
                return static::CLASS_ERROR;
        }

        return static::CLASS_DEFAULT;
    }
}
