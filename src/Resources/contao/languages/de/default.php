<?php

/**
 * Contao Open Source CMS.
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

use HeimrichHannot\LinkCheckerBundle\Manager\LinkChecker;

$arrLang = &$GLOBALS['TL_LANG']['linkChecker'];

/*
 * Messages
 */
$arrLang['statusCodes'][LinkChecker::STATUS_MAILTO] = 'E-Mail Adressen werden nicht geprüft.';
$arrLang['statusCodes'][LinkChecker::STATUS_INVALID] = 'Ungültige URL, kann nicht geprüft werden.';
