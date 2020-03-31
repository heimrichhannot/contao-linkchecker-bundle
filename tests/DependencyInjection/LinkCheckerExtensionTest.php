<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\LinkCheckerBundle\DependencyInjection\LinkCheckerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class LinkCheckerExtensionTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new LinkCheckerExtension();
        $extension->load([], $container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new LinkCheckerExtension();
        $this->assertInstanceOf(LinkCheckerExtension::class, $extension);
    }
}
