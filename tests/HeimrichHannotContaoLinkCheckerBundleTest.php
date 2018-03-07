<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\BeExplanationBundle\Tests;

use HeimrichHannot\LinkCheckerBundle\HeimrichHannotContaoLinkCheckerBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoLinkCheckerBundleTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoLinkCheckerBundle();

        $this->assertInstanceOf(HeimrichHannotContaoLinkCheckerBundle::class, $bundle);
    }
}