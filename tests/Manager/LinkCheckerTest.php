<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Tests\Manager;

use Contao\PageModel;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\LinkCheckerBundle\Manager\LinkChecker;
use HeimrichHannot\UtilsBundle\Request\CurlRequestUtil;

class LinkCheckerTest extends ContaoTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', '/');
        }

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'BE');
        }

        $GLOBALS['TL_LANG']['linkChecker']['statusCodes'] = [
            LinkChecker::STATUS_MAILTO => 'E-Mail Adressen werden nicht geprüft.',
            LinkChecker::STATUS_INVALID => 'Ungültige URL, kann nicht geprüft werden.',
            LinkChecker::STATUS_TIMEOUT => 'HTTP/1.0 408 Request Time-out',
        ];

        System::setContainer($this->getContainerWithContaoConfiguration());
    }

    private function createInstance()
    {
        $curlUtil = $this->createMock(CurlRequestUtil::class);
        return new LinkChecker($curlUtil);
    }

    public function testTest(): void
    {
        global $objPage;

        $objPage = $this->mockClassWithProperties(PageModel::class, ['outputFormat' => '']);

        $linkChecker = $this->createInstance();
        $result = $linkChecker->test('https://heimrich-hannot.de/');
        $this->assertSame('<span class="lc-status lc-success">HTTP/1.1 200 OK</span>', $result);

        $result = $linkChecker->test('heimricannot!"§$%&/()=');
        $this->assertSame('<span class="lc-status lc-default">Ungültige URL, kann nicht geprüft werden.</span>', $result);

        $result = $linkChecker->test('mailto:digitales@heimrich-hannot.de');
        $this->assertSame('<span class="lc-status lc-default">E-Mail Adressen werden nicht geprüft.</span>', $result);

        $result = $linkChecker->test('https://heimmmmrich-hannnnot.de/');
        $this->assertSame('<span class="lc-status lc-error">HTTP/1.0 408 Request Time-out</span>', $result);

        $result = $linkChecker->test(['https://heimmmmrich-hannnnot.de/', 'mailto:digitales@heimrich-hannot.de', 'https://heimrich-hannot.de/']);
        $this->assertSame([
            'https://heimmmmrich-hannnnot.de/' => '<span class="lc-status lc-error">HTTP/1.0 408 Request Time-out</span>',
            'mailto:digitales@heimrich-hannot.de' => '<span class="lc-status lc-default">E-Mail Adressen werden nicht geprüft.</span>',
            'https://heimrich-hannot.de/' => '<span class="lc-status lc-success">HTTP/1.1 200 OK</span>',
        ], $result);
    }
}
