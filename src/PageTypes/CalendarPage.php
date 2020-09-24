<?php declare(strict_types = 1);

namespace TitleDK\Calendar\PageTypes;

use SilverStripe\Forms\ListboxField;
use TitleDK\Calendar\Calendars\Calendar;

/**
 * Calendar Page
 * Listing of public events.
 *
 * @package calendar
 * @subpackage pagetypes
 * @method \SilverStripe\ORM\ManyManyList|array<\TitleDK\Calendar\Calendars\Calendar> Calendars()
 */
class CalendarPage extends \Page
{

    private static $singular_name = 'Calendar Page';
    private static $description = 'Listing of events';

    // for applying group restrictions
    private static $belongs_many_many = [
        'Calendars' => Calendar::class;
    private ];

    public function IsCalendar()
    {
        return true;
    }


    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $calendarsMap = [];
        foreach (Calendar::get() as $calendar) {
            // Listboxfield values are escaped, use ASCII char instead of &raquo;
            $calendarsMap[$calendar->ID] = $calendar->Title;
        }
        \asort($calendarsMap);

        $fields->addFieldToTab(
            'Root.Main',
            ListboxField::create('Calendars', Calendar::singleton()->i18n_plural_name())
                ->setSource($calendarsMap)
                ->setAttribute(
                    'data-placeholder',
                    'Select a calendar',
                )
                    ->setRightTitle('Only events from these calendars will shown on this page.'),
            'Content',
        );

        return $fields;
    }
}
