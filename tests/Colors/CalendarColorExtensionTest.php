<?php declare(strict_types = 1);

namespace TitleDK\Calendar\Tests\Colors;

use SilverStripe\Dev\SapphireTest;
use TitleDK\Calendar\Calendars\Calendar;

class CalendarColorExtensionTest extends SapphireTest
{
    /** @var \TitleDK\Calendar\Calendars\Calendar */
    private $calendar;

    public function setUp(): void
    {
        parent::setUp();

        $this->calendar = Calendar::create();
    }


    public function testTextColrDarkBackground(): void
    {
        $this->calendar->Color = '243456';
        $this->assertEquals('#fff', $this->calendar->TextColor());
    }


    public function testTextColrLightBackground(): void
    {
        $this->calendar->Color = 'DDDAAA';
        $this->assertEquals('#000', $this->calendar->TextColor());
    }


    public function testGetColorWithHash(): void
    {
        $this->calendar->Color = 'A48A48';
        $this->assertEquals('#A48A48', $this->calendar->getColorWithHash());

        $this->calendar->Color = '#A48A48';
        $this->assertEquals('#A48A48', $this->calendar->getColorWithHash());
    }


    public function testUpdateCMSFields(): void
    {
        $fields = $this->calendar->getCMSFields();

        /** @var \SilverStripe\Forms\TabSet $rootTab */
        $rootTab = $fields->fieldByName('Root');

        /** @var \SilverStripe\Forms\Tab $mainTab */
        $mainTab = $rootTab->fieldByName('Main');
        $fields = $mainTab->FieldList();
            $names = \array_map(
                static function ($field) {
                    return $field->Name;
                },
                $fields->toArray(),
            );

            // @todo fix this test
        $this->assertEquals(
            [],
            array_diff(
                [
                'Slug',
                'Title',
                'Color',
                'Groups',
                ],
                $names
            )
        );
    }
}
