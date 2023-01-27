<?php declare(strict_types = 1);

namespace TitleDK\Calendar\Categories;

use SilverStripe\ORM\DataList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

/**
 * Public Event Category
 *
 * @package calendar
 * @subpackage categories
 */
class PublicEventCategory extends EventCategory
{

    /** @return \SilverStripe\ORM\DataList<\TitleDK\Calendar\Categories\Event> */
    public function ComingEvents(string $from = null): DataList
    {
        return $this->Events()
            ->filter(
                [
                'StartDateTime:GreaterThan' => \date('Y-m-d', $from ? \strtotime($from) : \time()),
                ]
            );
    }


    /**
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return true;
    }


    /**
     * @param Member $member
     * @param array $context Additional context-specific data which might
     * affect whether (or where) this object could be created.
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return $this->canManage($member);
    }


    /**
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return $this->canManage($member);
    }


    /**
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return $this->canManage($member);
    }


    protected function canManage(Member $member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member) || Permission::check('EVENTCATEGORY_MANAGE', 'any', $member);
    }
}
