<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Back end form fields
 */
$GLOBALS['BE_FFL']['linkChecker'] = 'HeimrichHannot\LinkCheckerBundle\Widget\LinkChecker';

/**
 * Assets
 */
$GLOBALS['TL_JAVASCRIPT']['linkchecker']      = 'bundles/heimrichhannotcontaolinkchecker/js/linkchecker.min.js|static';

if (System::getContainer()->get('huh.utils.container')->isBackend()) {
    $GLOBALS['TL_CSS']['linkchecker'] = 'bundles/heimrichhannotcontaolinkchecker/css/be_linkchecker.min.css|static';
}

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][] = ['HeimrichHannot\LinkCheckerBundle\Widget\LinkChecker', 'executePreActionsHook'];