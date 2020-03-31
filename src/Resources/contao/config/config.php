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
$GLOBALS['BE_FFL']['linkChecker'] = \HeimrichHannot\LinkCheckerBundle\Widget\LinkCheckerWidget::class;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['executePreActions'][] = [\HeimrichHannot\LinkCheckerBundle\EventListener\ExecutePreActionsListener::class, 'onExecutePreActions'];