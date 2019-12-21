<?php

namespace TitleDK\Calendar\Tests\Categories;

use \SilverStripe\Dev\SapphireTest;
use TitleDK\Calendar\Categories\PublicEventCategory;

class PublicEventCategoryTest extends SapphireTest
{
    protected static $fixture_file = 'tests/events.yml';

    /** @var PublicEventCategory */
    private $category1;

    /** @var PublicEventCategory */
    private $category2;

    public function setUp()
    {
        // this has to happen before accessing the fixtures
        parent::setUp();

        $this->category1 = $this->objFromFixture('TitleDK\Calendar\Categories\PublicEventCategory', 'category1');
        $this->category2 = $this->objFromFixture('TitleDK\Calendar\Categories\PublicEventCategory', 'category2');
    }

    public function testComingEvents()
    {
        $category1 = $this->objFromFixture('TitleDK\Calendar\Categories\PublicEventCategory', 'category1');

        // category 1 has a past and 2 future events, only the future events should show
        $events = $this->category1->comingEvents('2019-12-30')->toArray();
        $titles = array_map(function($event) {
            return $event->Title;
        }, $events);
        $this->assertEquals(['Happy New Year!!', 'Chilling in the Future'], $titles);

        // category 2 has onlhy the event way in the future
        $events = $this->category2->comingEvents('2019-12-30')->toArray();
        $titles = array_map(function($event) {
            return $event->Title;
        }, $events);
        $this->assertEquals(['Chilling in the Future'], $titles);
    }

    public function testCanView()
    {
        $this->assertTrue($this->category1->canView());
    }

    public function testCanCreate()
    {
        $this->assertTrue($this->category1->canCreate());
    }

    public function testCanEdit()
    {
        $this->assertTrue($this->category1->canEdit());
    }

    public function testCanDelete()
    {
        $this->assertTrue($this->category1->canDelete());
    }

}
