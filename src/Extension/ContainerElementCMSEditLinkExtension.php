<?php

namespace Antlion\ElementalContainer\Extension;


use DNADesign\Elemental\Models\BaseElement;
use Antlion\ElementalContainer\Model\ElementContainer;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension; 

/**
 * BaseElement can be nested, CMSEditLink() needs to be updated to reflect that
 *
 * @property ContainerElementCMSEditLinkExtension|$this $owner
 */
class ContainerElementCMSEditLinkExtension extends Extension
{
    /**
     * @param string $link
     */
    public function updateCMSEditLink(?string &$link): void
    {
        /** @var $owner BaseElement */
        $owner = $this->owner;

        $relationName = $owner->getAreaRelationName();
        $page = $owner->getPage();

        if (!$page) {
            return;
        }

        if ($page instanceof ElementContainer) {
            // nested bock - we need to get edit link of parent block
            $link = Controller::join_links(
                $page->CMSEditLink(),
                'ItemEditForm/field/' . $page->getOwnedAreaRelationName() . '/item/',
                $owner->ID
            );

            // remove edit link from parent CMS link
            $link = preg_replace('/\/item\/([\d]+)\/edit/', '/item/$1', $link);
        } else {
            // block is directly under a non-block object - we have reached the top of nesting chain
            $link = Controller::join_links(
                singleton(CMSPageEditController::class)->Link('EditForm'),
                $page->ID,
                'field/' . $relationName . '/item/',
                $owner->ID
            );
        }

        $link = Controller::join_links(
            $link,
            'edit'
        );
    }
}
