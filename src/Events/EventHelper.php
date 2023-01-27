<?php declare(strict_types = 1);

namespace TitleDK\Calendar\Events;

use Carbon\Carbon;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use TitleDK\Calendar\DateTime\DateTimeHelper;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * Event Helper
 * Helper class for event related calculations and formatting
 *
 * @package calendar
 */
class EventHelper
{

    use DateTimeHelper;

    /**
     * Date format for YMD
     *
     * @todo move to config
     */
    public const YMD_DATE_FORMAT='Y-m-d';

    /**
     * Formatted Dates
     * Returns either the event's date or both start and end date if the event spans more than
     * one date
     *
     * Format:
     * Jun 7th - Jun 10th
     *
     * @param \SilverStripe\ORM\FieldType\DBDatetime $startObj
     * @param \SilverStripe\ORM\FieldType\DBDatetime $endObj
     */
    public static function formattedDates(DBDatetime $startObj, DBDatetime $endObj): string
    {
        $str = '';

        if ($startObj->value && $endObj->value) {
            //Checking if end date is set
            $endValue = $endObj->getValue();
            $endDateIsset = isset($endValue);

            $startTime = \strtotime($startObj->value);
            $endTime = \strtotime($endObj->value);

            $startMonth = \date('M', $startTime);

            // include ordinal, e.g. 1st, 4th
            $startDayOfMonth = $startObj->DayOfMonth(true);

            $str = $startMonth . ' ' . $startDayOfMonth ;

            if (\date(self::YMD_DATE_FORMAT, $startTime) === \date(self::YMD_DATE_FORMAT, $endTime)) {
                //one date - str. has already been written
            } else {
                //two dates

                if ($endDateIsset) {
                    $endMonth = \date('M', $endTime);

                    // include ordinal, e.g. 1st, 4th
                    $endDayOfMonth = $endObj->DayOfMonth(true);

                    if ($startMonth === $endMonth) {
                        $str .= ' - ' . $endDayOfMonth;
                    } else {
                        $str .= ' - ' . $endMonth . ' ' . $endDayOfMonth;
                    }
                }
            }
        }

        return $str;
    }


    /**
     * @param Object|\SilverStripe\ORM\FieldType\DBField $startObj
     * @return false|string
     */
    public static function formattedStartDate($startObj)
    {
        $startTime = \strtotime($startObj->value);

        return \date('M j, Y', $startTime);
    }


    /**
     * @param Object|\SilverStripe\ORM\FieldType\DBField $startObj
     * @param Object|\SilverStripe\ORM\FieldType\DBField $endObj
     */
    public static function formattedAllDates($startObj, $endObj):? DBField
    {
        $startDate = \date(self::YMD_DATE_FORMAT, \strtotime($startObj->value));
        $endDate = \date(self::YMD_DATE_FORMAT, \strtotime($endObj->value));

        if ($startDate === $endDate) {
            return null;
        }

        $startTime = \strtotime($startObj->value);
        $endTime = \strtotime($endObj->value);

        // @todo This should be a separate helper method
        //
        // Note that the end date time is set when editing, this needs imported also
        $startDate = \date('g:ia', $startTime) === '12:00am' ? \date('M j F, Y', $startTime) :
            \date('M j, Y (g:ia)', $startTime);

        // @todo see note above
        // @tod null date passes this test without the addition of empty
        if (\date('g:ia', $endTime) === '12:00am' && isset($endDate)) {
            $endDate = \date('M j, Y', $endTime);
        } else {
            // This is the straddling midnight case
            // time zones do not matter here, it is the delta in hours that is required
            $startDateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $startObj->value);
            $endDateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $endObj->value);

            $durationHrs = $endDateCarbon->diffInHours($startDateCarbon);
            $endDate = \date('M j, Y', $endTime) . '  (' . $durationHrs . ' hrs)';
        }

        return DBField::create_field(DBHTMLText::class, "$startDate &ndash; $endDate");
    }


    /**
     * Formatted time frame
     * Returns either a string or null
     * Time frame is only applicable if both start and end time is on the same day
     *
     * @param \SilverStripe\ORM\FieldType\DBDatetime $startDBDateTime
     * @param \SilverStripe\ORM\FieldType\DBDatetime $endDBDateTime
     */
    public static function formattedTimeframe(DBDatetime $startDBDateTime, DBDatetime $endDBDateTime): ?string
    {
        $str = '';

        if ($startDBDateTime->value && $endDBDateTime->value) {
            $startTime = \strtotime($startDBDateTime->value);
            $endTime = \strtotime($endDBDateTime->value);

            if ($startTime === $endTime) {
                return null;
            }

            if ($endTime) {
                //time frame is only applicable if both start and end time is on the same day
                if (\date('Y-m-d', $startTime) === \date('Y-m-d', $endTime)) {
                    $str = \date('g:ia', $startTime) . ' - ' . \date('g:ia', $endTime);
                }
            } else {
                $str = \date('g:ia', $startTime);
            }
        }

        return $str;
    }
}
