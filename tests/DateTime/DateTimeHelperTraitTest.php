<?php declare(strict_types = 1);

namespace TitleDK\Calendar\Tests\DateTime;

use Carbon\Carbon;
use SilverStripe\Dev\SapphireTest;
use TitleDK\Calendar\DateTime\DateTimeHelper;

class DateTimeHelperTraitTest extends SapphireTest
{

    use DateTimeHelper;

    public function setUp(): void
    {
        parent::setUp();

        // fix the concept of now for testing purposes
        $this->now = Carbon::create(2018, 5, 16, 8, 20);
        Carbon::setTestNow($this->now);
    }


    public function testCarbonDateTime(): void
    {
        $carbon = $this->carbonDateTime('2018-05-21 13:04:00');
        $this->assertEquals('2018', $carbon->year);
        $this->assertEquals('05', $carbon->month);
        $this->assertEquals('21', $carbon->day);
        $this->assertEquals('13', $carbon->hour);
        $this->assertEquals('04', $carbon->minute);
        $this->assertEquals('00', $carbon->second);
    }


    public function testGetSSDateTimeFromCarbon(): void
    {
        $this->assertEquals('2018-05-16 08:20:00', $this->getSSDateTimeFromCarbon($this->now));
    }
}
