<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Widget;

use Contao\BackendTemplate;
use Contao\Environment;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class LinkCheckerWidget extends Widget
{
    const LINKCHECKER_PARAM = 'lc'; // The param within the url, that holds the test url
    const LINKCHECKER_TEST_ACTION = 'lc-test'; // The back end action, required for ajax request
    /**
     * Submit user input.
     *
     * @var bool
     */
    protected $blnSubmitInput = false;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_linkchecker_widget';

    protected $arrLinks = [];

    public function generate(): string
    {
        $GLOBALS['TL_JAVASCRIPT']['linkchecker'] = 'bundles/heimrichhannotcontaolinkchecker/js/linkchecker.min.js|static';
        $GLOBALS['TL_CSS']['linkchecker'] = 'bundles/heimrichhannotcontaolinkchecker/css/be_linkchecker.min.css|static';

        if ($this->collect()) {
            return $this->test();
        }

        return '';
    }

    protected function test(): string
    {
        $objTemplate = new BackendTemplate('be_linkchecker');

        $rand = random_int(0, mt_getrandmax());

        $arrLinks = [];

        for ($i = 0, $c = \count($this->arrLinks); $i < $c; ++$i) {
            $url = System::getContainer()->get(Utils::class)->routing()->generateBackendRoute(
                ['action' => static::LINKCHECKER_TEST_ACTION, static::LINKCHECKER_PARAM => $this->arrLinks[$i]]
            );
            $url = Environment::get('url').$url;

            $objLink = new \stdClass();
            $objLink->url = urldecode($url);
            $objLink->title = $this->arrLinks[$i];
            $objLink->testUrl = $this->arrLinks[$i].'#'.$rand.$i;
            $objLink->targetID = 'lc-'.$rand.$i;
            $objLink->target = '#lc-'.$rand.$i;
            $objLink->text = StringUtil::substr($this->arrLinks[$i], 100);
            $arrLinks[$i] = $objLink;
            unset($this->arrLinks[$i]);
        }

        $objTemplate->links = $arrLinks;

        return $objTemplate->parse();
    }

    protected function collect(): bool
    {
        // string
        $this->arrLinks = $this->value;

        // array
        if (\is_array($this->value)) {
            $this->arrLinks = $this->value;

            return true;
        }

        // html code
        if (str_contains($this->value, '<')) {
            $this->arrLinks = [];
            $objCrawler = new HtmlPageCrawler($this->value);

            // collect only inside body
            if ($objCrawler->isHtmlDocument()) {
                $objCrawler->filter('body');
            }

            try {
                // container
                $objCrawler->filter('a[href]')->each(function ($node, $i) {
                    /* @var $node  HtmlPageCrawler */
                    $this->addLinkFromNode($node);
                });
            } catch (SyntaxErrorException) {
            }
        }

        return \is_array($this->arrLinks) && !empty($this->arrLinks);
    }

    /**
     * Add links from node to test list.
     */
    protected function addLinkFromNode(HtmlPageCrawler $node)
    {
        if (empty($node->attr('href'))) {
            return;
        }

        $this->arrLinks[] = $node->attr('href');
    }
}
