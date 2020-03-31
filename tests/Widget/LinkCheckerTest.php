<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\Tests\Widget;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\LinkCheckerBundle\Widget\LinkCheckerWidget;

class LinkCheckerTest extends ContaoTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGenerate()
    {
        $widget = new LinkCheckerWidget();

        $result = $widget->generate();
        $this->assertNull($result);
    }
}
