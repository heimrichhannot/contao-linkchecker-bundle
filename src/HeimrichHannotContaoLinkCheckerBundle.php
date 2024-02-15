<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle;

use HeimrichHannot\LinkCheckerBundle\DependencyInjection\LinkCheckerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoLinkCheckerBundle extends Bundle
{
    public function getContainerExtension(): LinkCheckerExtension
    {
        return new LinkCheckerExtension();
    }
}
