<?php
namespace TitleDK\Calendar\Events;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\CsvBulkLoader;
use TitleDK\Calendar\Calendars\Calendar;
use TitleDK\Calendar\Categories\EventCategory;

/**
 * PlayerCsvBulkLoader
 *
 * @author Anselm Christophersen <ac@anselm.dk>
 * @date   October 2015
 */
class EventCsvBulkLoader extends CsvBulkLoader
{

    private static $dateFormat = 'm/d/Y';
    private static $timeFormat = 'H:i';

    public $columnMap = array(
        'Title' => 'Title',
        'Start Date' => '->importStartDate',
        'Start Time' => '->importStartTime',
        'End Date' => '->importEndDate',
        'End Time' => '->importEndTime',
        'Calendar' => 'Calendar.Title'
    );

    /**
     * @var array
     */
    public $relationCallbacks = array(
        'Calendar.Title' => array(
            'relationname' => 'Calendar',
            'callback' => 'getCalendarByTitle'
        )
    );


    public function getImportSpec()
    {
        $spec = array();
        $dateFormat = Config::inst()->get('EventCsvBulkLoader', 'dateFormat');

        /*
         * Fields
         */
        $spec['fields'] = array(
            'Title' => _t('Event.Title', 'Title'),
            'Start Date' => _t(
                'Event.StartDateSpec',
                'Start date in format {dateformat}',
                '',
                ['dateformat' => $dateFormat]
            ),
            'Start Time' => _t('Event.StartTime', 'Start Time'),
            'End Date' => _t(
                'Event.EndDateSpec',
                'End date in format {dateformat}'.'',
                ['dateformat' => $dateFormat]
            ),
            'End Time' => _t('Event.EndTime', 'End Time')
        );

        /*
         * Relations
         */
        $relations = array();
        if (Config::inst()->get(Calendar::class, 'enabled')) {
            $relations['Calendar'] =  _t('Event.CalendarTitle', 'Calendar title');
        }

        if (Config::inst()->get(EventCategory::class, 'enabled')) {
            $relations['Categories'] =  _t('Event.CategoryTitles', 'Category titles');
        }

        $spec['relations'] = $relations;

        return $spec;
    }
    /**
     * @param  $val
     * @return string|DateTime
     */
    protected static function importDate($val, $rt = 'string')
    {
        $dateFormat = Config::inst()->get(EventCsvBulkLoader::class, 'dateFormat');
        $dateFormat .= ' ' . 'H:i';
        //$val = $val . '0:00';
        $dateTime = date_create_from_format($dateFormat , $val);

        if ($rt == 'string') {
            return $dateTime->format('Y-m-d H:i:s');
        } else {
            return $dateTime;
        }
    }

    /**
     * @param $obj
     * @param $val
     * @param $record
     */
    public static function importStartDate(&$obj, $val, $record)
    {
        $dateTime = self::importDate($val);
        $obj->TimeFrameType = 'DateTime';
        $obj->StartDateTime = $dateTime;
        $obj->AllDay = true;
    }

    /**
     * @param $obj
     * @param $val
     * @param $record
     */
    public static function importStartTime(&$obj, $val, $record)
    {
        if (!strlen($val)) {
            return;
        }
        $dt = new \DateTime($obj->StartDateTime);
        $date = $dt->format('Y-m-d');
        $obj->StartDateTime = $date . ' ' . $val;
        $obj->AllDay = false;
    }

    /**
     * @param $obj
     * @param $val
     * @param $record
     */
    public static function importEndDate(&$obj, $val, $record)
    {
        $dateTime = self::importDate($val);
        $obj->EndDateTime = $dateTime;
    }

    /**
     * @param $obj
     * @param $val
     * @param $record
     */
    public static function importEndTime(&$obj, $val, $record)
    {
        if (!strlen($val)) {
            return;
        }
        $dt = new \DateTime($obj->EndDateTime);
        $date = $dt->format('Y-m-d');
        $obj->EndDateTime = $date . ' ' . $val;
    }

    /**
     * @param  $obj
     * @param  $val
     * @param  $record
     * @return DataObject
     */
    public static function findOrCreateCalendarByTitle(&$obj, $val, $record)
    {
        $c = Calendar::get()->filter('Title', $val)->First();
        if ($c && $c->exists()) {
            return $c;
        } else {
            $c = new Calendar();
            $c->Title = $val;
            $c->write();
            return $c;
        }
    }
}
