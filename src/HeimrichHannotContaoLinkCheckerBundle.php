<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle;

use HeimrichHannot\BeExplanationBundle\DependencyInjection\LinkCheckerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoLinkCheckerBundle extends Bundle
{
    /**
     * @return LinkCheckerExtension
     */
    public function getContainerExtension()
    {
        return new LinkCheckerExtension();
    }
}
