<?php

namespace HeimrichHannot\LinkCheckerBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\LinkCheckerBundle\HeimrichHannotContaoLinkCheckerBundle;

class EncoreExtension implements EncoreExtensionInterface
{
    public function getBundle(): string
    {
        return HeimrichHannotContaoLinkCheckerBundle::class;
    }

    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-linkchecker-bundle', 'src/Resources/public/js/contao-linkchecker-bundle.es6.js')
                ->addJsEntryToRemoveFromGlobals('linkchecker'),
        ];
    }
}
