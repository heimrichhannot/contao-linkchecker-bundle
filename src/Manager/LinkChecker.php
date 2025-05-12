<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LinkChecker
{
    public const STATUS_MAILTO = 'mailto';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_TIMEOUT = '408';

    public const CLASS_DEFAULT = 'lc-default';
    public const CLASS_INFO = 'lc-info';
    public const CLASS_SUCCESS = 'lc-success';
    public const CLASS_ERROR = 'lc-error';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ContaoFramework $contaoFramework,
    ) {
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

        $response = $this->client->request(Request::METHOD_GET, $url);

        return $this->getResult($response->getStatusCode());
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
        $objTemplate = $this->contaoFramework->createInstance(
            FrontendTemplate::class,
            ['linkchecker_result_default']
        );

        $text = $result;

        if (isset($GLOBALS['TL_LANG']['linkChecker']['statusCodes'][$result])) {
            $text = $GLOBALS['TL_LANG']['linkChecker']['statusCodes'][$result];
        } elseif (isset(Response::$statusTexts[$result])) {
            $text = Response::$statusTexts[$result] . ' (Statuscode: ' . $result . ')';
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

        return match ($intStart) {
            '1', '3' => static::CLASS_INFO,
            '2' => static::CLASS_SUCCESS,
            '4', '5' => static::CLASS_ERROR,
            default => static::CLASS_DEFAULT,
        };
    }
}
