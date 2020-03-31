<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\BeExplanationBundle\Tests;

use HeimrichHannot\LinkCheckerBundle\DependencyInjection\LinkCheckerExtension;
use HeimrichHannot\LinkCheckerBundle\HeimrichHannotContaoLinkCheckerBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoLinkCheckerBundleTest extends TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new HeimrichHannotContaoLinkCheckerBundle();

        $this->assertInstanceOf(LinkCheckerExtension::class, $bundle->getContainerExtension());
    }
}
