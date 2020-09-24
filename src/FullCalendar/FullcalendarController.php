<?php declare(strict_types = 1);

namespace TitleDK\Calendar\FullCalendar;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;
use TitleDK\Calendar\Events\Event;

/**
 * Fullcalendar controller
 * Controller/API, used for interacting with the fullcalendar js plugin
 *
 * @package calendar
 * @subpackage fullcalendar
 */
class FullcalendarController extends Controller
{

    protected $event = null;
    protected $start = null;
    protected $end = null;
    protected $allDay = false;
    protected $member = null;

    private static $allowed_actions = array(
        'events';
    private );


    public function init(): void
    {
        parent::init();

        $this->member = Security::getCurrentUser();

        $request = $this->getRequest();

        //Setting dates based on request variables
        //We could add some sanity check here
        $this->start = $request->getVar('start');
        $this->end = $request->getVar('end');

        // @todo This does not appear to be used
        if ($request->getVar('allDay') === 'true') {
            $this->allDay = true;
        }

        //Setting event based on request vars
        if ((!$eventID = (int) $request->getVar('eventID')) || ($eventID <= 0)) {
            return;
        }

        $event = Event::get()
            ->byID($eventID);
        if (!$event || !$event->exists()) {
            return;
        }

        $this->event = $event;
    }


    /**
     * Calculate start/end date for event list
     * Currently set to offset of 30 days
     *
     * @param string $type ("start"/"end")
     * @param int $timestamp
     * return \SS_Datetime
     */
    public function eventlistOffsetDate(string $type, int $timestamp, $offset = 30)
    {
        return self::offset_date($type, $timestamp, $offset);
    }


    /**
     * Handles returning the JSON events data for a time range.
     *
     * @param \SilverStripe\Control\HTTPRequest $request
     */
    public function events(HTTPRequest $request): HTTPResponse
    {
        /** @var string $calendars comma separated list of calendar IDs to show events for */
        $calendars = $request->getVar('calendars');

        /** @var string $offset days to offset by */
        $offset = empty($request->getVar('offset'))
            ? 30
            : $request->getVar('offset');

        $filter = array(
            'StartDateTime:GreaterThan' => $this->eventlistOffsetDate('start', $request->postVar('start'), $offset),
            'EndDateTime:LessThan' => $this->eventlistOffsetDate('end', $request->postVar('end'), $offset),
        );

        // start a query for events
        $events = Event::get()
            ->filter(
                $filter,
            );

        // filter by calendar ids if they have been provided
        if ($calendars) {
            $calIDList = \explode(',', $calendars);
            $events = $events->filter('CalendarID', $calIDList);
        }

        $result = array();
        if ($events->count() > 0) {
            foreach ($events as $event) {
                $calendar = $event->Calendar();

                //default
                $bgColor = '#999';
                $borderColor = '#555';

                // @todo This is an error in that it enforces use of the color extension.  May as well just have it
                // there by default
                if ($calendar->exists()) {
                    $bgColor = $calendar->getColorWithHash();
                    $borderColor = $calendar->getColorWithHash();
                }

                $resultArr = self::format_event_for_fullcalendar($event);
                $resultArr = \array_merge(
                    $resultArr,
                    array(
                    'backgroundColor' => $bgColor,
                    'textColor' => '#FFF',
                    'borderColor' => $borderColor,
                    ),
                );
                $result[] = $resultArr;
            }
        }

        $response = new HTTPResponse(\json_encode($result));
        $response->addHeader('Content-Type', 'application/json');

        return $response;
    }


    /**
     * AJAX Json Response handler
     *
     * @param array|null $retVars
     */
    public function handleJsonResponse(bool $success = false, ?array $retVars = null): HTTPResponse
    {
        $result = array();
        if ($success) {
            $result = array(
                'success' => $success
            );
        }
        if ($retVars) {
            $result = \array_merge($retVars, $result);
        }

        $response = new HTTPResponse(\json_encode($result));
        $response->addHeader('Content-Type', 'application/json');

        return $response;
    }


    /**
     * Calculate start/end date for event list
     *
     * @todo this should go in a helper class
     */
    public static function offset_date(string $type, int $timestamp, $offset = 30)
    {
        if (!$timestamp) {
            $timestamp = \time();
        }

        // check whether the timestamp was
        // given as a date string (2016-09-05)
        if (\strpos($timestamp, "-") > 0) {
            $timestamp = \strtotime($timestamp);
        }

        //days in secs
        $offsetCalc = $offset * 24 * 60 * 60;

        $offsetTime = null;
        if ($type === 'start') {
            $offsetTime = $timestamp - $offsetCalc;
        } elseif ($type === 'end') {
            $offsetTime = $timestamp + $offsetCalc;
        }

        return \date('Y-m-d', $offsetTime);
    }


    /**
     * Format an event to comply with the fullcalendar format
     *
     * @todo Move to a helper
     */
    public static function format_event_for_fullcalendar(Event $event)
    {
        //default
        $bgColor = '#999';
        $borderColor = '#555';

        return array(
            'id' => $event->ID,
            'title' => $event->Title,
            'start' => self::format_datetime_for_fullcalendar($event->StartDateTime),
            'end' => self::format_datetime_for_fullcalendar($event->EndDateTime),
            'allDay' => $event->isAllDay(),
            'className' => $event->ClassName,
            //event calendar
            'backgroundColor' => $bgColor,
            'textColor' => '#FFFFFF',
            'borderColor' => $borderColor,
        );
    }


    /**
     * Format SS_Datime to fullcalendar format
     *
     * @todo Move to a helper
     */
    public static function format_datetime_for_fullcalendar(string $datetime)
    {
        $time = \strtotime($datetime);

        return \date('c', $time);
    }
}
