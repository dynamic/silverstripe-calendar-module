<?php declare(strict_types = 1);

namespace TitleDK\Calendar\Helpers;

use Carbon\Carbon;
use SilverStripe\ORM\DataObject;
use TitleDK\Calendar\Calendars\Calendar;
use TitleDK\Calendar\Core\CalendarHelper;
use TitleDK\Calendar\DateTime\DateTimeHelperTrait;

class CalendarPageHelper
{

    use DateTimeHelperTrait;

    public function realtimeMonthDay()
    {
        return \date('Y-m-d', Carbon::now()->timestamp);
    }


    public function realtimeMonth()
    {
        return \date('Y-m', Carbon::now()->timestamp);
    }


    /**
     * If a month paramater is set, such as 2020-01 use that, otherwise use Carbon::now(), the current time, as the
     * basis for forming an equivalent string
     */
    public function currentContextualMonth(): string
    {
        return isset($_GET['month'])
            ? $_GET['month']
            : $this->realtimeMonth();
    }


    // @todo This is inconsistent with the JavaScript which uses the full month name
    public function currentContextualMonthStr()
    {
        $month = $this->currentContextualMonth();
        $t = \strtotime($month);

        return \date('M Y', $t);
    }


    public function previousContextualMonth()
    {
        $month = $this->currentContextualMonth();
        $t = \strtotime($month);
        $prev = \strtotime('-1 month', $t);

        return \date('Y-m', $prev);
    }


    public function nextContextualMonth()
    {
        $month = $this->currentContextualMonth();
        $t = \strtotime($month);
        $next = \strtotime('+1 month', $t);

        return \date('Y-m', $next);
    }


    // ---- events related ----

    /**
     * Recent events are only related to 'now', not any contextual month from a GET param
     *
     * @param array $calendarIDs calendar IDs to pull events from
     * @return \SilverStripe\ORM\DataList recent events
     */
    public function recentEvents(array $calendarIDs): \SilverStripe\ORM\DataList
    {
        $now = $this->realtimeMonthDay();
        $prev = \strtotime('-1 month', \time());
        $oneMonthAgo = \date('Y-m-d', $prev);

        // This method takes a csv of IDs, not an array.
        return CalendarHelper::events_for_date_range($oneMonthAgo, $now, $calendarIDs)
            ->sort('StartDateTime DESC');
    }


    /**
     * Upcoming events are related to the contextual month, this method is called to get the upcoming events from
     * a given point in time, i.e. a contextual month
     *
     * @param array $calendarIDs calendar IDs to pull events from
     * @return \SilverStripe\ORM\DataList recent events
     */
    public function upcomingEvents(array $calendarIDs): \SilverStripe\ORM\DataList
    {
        $calendar = DataObject::get_by_id(Calendar::class, $calendarIDs[0]);
        $events = $calendar->Events();

        $currentContextualMonth = $this->currentContextualMonth();
        $now = $this->realtimeMonthDay();
        $nowMonth = \substr($now, 0, 7);

        // if nowMonth is the same as the current month, as in realtime month

        $start = null;

        $start = $currentContextualMonth === $nowMonth
            ? $now
            : $currentContextualMonth . '-01';

        $startCarbon = $this->carbonDateTime($start .' 00:00:00')->timestamp;
        $next = \strtotime('+1 month', $startCarbon);
        $inOneMonth = \date('Y-m-d', $next);

        // This method takes a csv of IDs, not an array.
        return CalendarHelper::events_for_date_range($start, $inOneMonth, $calendarIDs)
            ->sort('"StartDateTime" ASC');
    }


    public function performSearch($query)
    {
        // @todo This is case sensitive with Postgresql, not so with MySQL
        //$query = strlower(addslashes($query));
        $query = (\addslashes($query));
        //Debug::dump($query);
        $qarr = \preg_split('/[ +]/', $query);

        $filter = '';
        $first = true;
        foreach ($qarr as $qitem) {
            if (!$first) {
                $filter .= " AND ";
            }

            $filter .= " (
					\"Title\" LIKE '%$qitem%'
					OR \"Details\" LIKE '%$qitem%'
				)";
            $first = false;
        }

        // @todo this is incorrect, does not take into context of current calendars
        return CalendarHelper::all_events()
            ->where($filter);
    }
}
