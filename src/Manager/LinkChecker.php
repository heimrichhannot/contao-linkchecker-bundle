<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Manager;

use Contao\FrontendTemplate;
use Contao\System;
use Contao\Validator;

class LinkChecker
{
    const STATUS_MAILTO = 'mailto';
    const STATUS_INVALID = 'invalid';
    const STATUS_TIMEOUT = '408';

    const CLASS_DEFAULT = 'lc-default';
    const CLASS_INFO = 'lc-info';
    const CLASS_SUCCESS = 'lc-success';
    const CLASS_ERROR = 'lc-error';

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
     * @param string $strUrl The url
     *
     * @return bool|mixed The translated status code, or false if the link was not tested
     */
    protected function testOne(string $url)
    {
        if (System::getContainer()->get('huh.utils.string')->startsWith($url, 'mailto:')) {
            return $this->getResult(static::STATUS_MAILTO);
        }

        if (!Validator::isUrl($url)) {
            return $this->getResult(static::STATUS_INVALID);
        }

        list($headers, $body) = System::getContainer()->get('huh.utils.request.curl')->request($url, [], true);

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
    protected function testAll(array $arrLinks)
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
     *
     * @param $strResult
     *
     * @return mixed
     */
    protected function getResult(string $result)
    {
        $curlUtil = System::getContainer()->get('huh.utils.request.curl');

        $objTemplate = new FrontendTemplate('linkchecker_result_default');

        $text = $result;

        if (isset($GLOBALS['TL_LANG']['linkChecker']['statusCodes'][$result])) {
            $text = $GLOBALS['TL_LANG']['linkChecker']['statusCodes'][$result];
        } elseif ($curlUtil::HTTP_STATUS_CODE_MESSAGES[$result]) {
            $text = $curlUtil::HTTP_STATUS_CODE_MESSAGES[$result].' (Statuscode: '.$result.')';
        }

        $objTemplate->text = $text;

        $objTemplate->status = $this->getStatusClass($result);

        return $objTemplate->parse();
    }

    /**
     * Get the status class for a given result.
     *
     * @return string
     */
    protected function getStatusClass(string $statusCode)
    {
        $intStart = null;

        if (\strlen($statusCode) > 0) {
            $intStart = substr($statusCode, 0, 1);
        }

        switch ($intStart) {
            //1xx Informational
            case '1':
                return static::CLASS_INFO;
            //2xx Success
            case '2':
                return static::CLASS_SUCCESS;
            //3xx Redirection
            case '3':
                return static::CLASS_INFO;
            //4xx Client Error
            case '4':
                return static::CLASS_ERROR;
            //5xx Server Error
            case '5':
                return static::CLASS_ERROR;
        }

        return static::CLASS_DEFAULT;
    }
}
