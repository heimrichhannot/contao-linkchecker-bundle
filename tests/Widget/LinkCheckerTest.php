<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Tests\Widget;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\LinkCheckerBundle\Widget\LinkCheckerWidget;

class LinkCheckerTest extends ContaoTestCase
{
    public function testGenerate(): void
    {
        $container = $this->getContainerWithContaoConfiguration();
        System::setContainer($container);

        $widget = new LinkCheckerWidget();

        $result = $widget->generate();
        $this->assertNull($result);
    }
}
