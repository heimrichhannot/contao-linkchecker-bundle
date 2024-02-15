<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\LinkCheckerBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\AjaxBundle\Response\ResponseData;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\LinkCheckerBundle\Manager\LinkChecker as LinkCheckerManager;
use HeimrichHannot\LinkCheckerBundle\Widget\LinkCheckerWidget;
use Symfony\Component\HttpFoundation\RequestStack;

class ExecutePreActionsListener
{
    private LinkCheckerManager $linkChecker;
    private RequestStack $requestStack;

    /**
     * ExecutePreActionsListener constructor.
     */
    public function __construct(LinkCheckerManager $linkChecker, RequestStack $requestStack)
    {
        $this->linkChecker = $linkChecker;
        $this->requestStack = $requestStack;
    }

    /**
     * @Hook("executePreActions")
     */
    public function onExecutePreActions(string $action): void
    {
        if (LinkCheckerWidget::LINKCHECKER_TEST_ACTION !== $action) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request->request->has(LinkCheckerWidget::LINKCHECKER_PARAM)) {
            return;
        }

        $strStatus = $this->linkChecker->test($request->request->get(LinkCheckerWidget::LINKCHECKER_PARAM));

        $objResponse = new ResponseSuccess();
        $objResponse->setResult(new ResponseData($strStatus));
        $objResponse->output();
    }
}
