<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @author  Thomas Körner <t.koerner@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */


namespace HeimrichHannot\LinkCheckerBundle\EventListener;


use HeimrichHannot\AjaxBundle\Response\ResponseData;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\LinkCheckerBundle\Manager\LinkChecker as LinkCheckerManager;
use HeimrichHannot\LinkCheckerBundle\Widget\LinkCheckerWidget;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;

class ExecutePreActionsListener
{
    /**
     * @var LinkCheckerWidget
     */
    private $linkChecker;
    /**
     * @var Request
     */
    private $request;

    /**
     * ExecutePreActionsListener constructor.
     */
    public function __construct(Request $request, LinkCheckerManager $linkChecker)
    {
        $this->linkChecker = $linkChecker;
        $this->request = $request;
    }

    /**
     * @Hook("executePreActions")
     */
    public function onExecutePreActions(string $action): void
    {
        if ($action !== LinkCheckerWidget::LINKCHECKER_TEST_ACTION) {
            return;
        }

        if (!$this->request->hasPost(LinkCheckerWidget::LINKCHECKER_PARAM)) {
            return;
        }

        $strStatus = $this->linkChecker->test($this->request->getPost(LinkCheckerWidget::LINKCHECKER_PARAM));

        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData($strStatus));
        $objResponse->output();
    }
}