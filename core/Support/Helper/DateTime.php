<?php

namespace Core\Support\Helper;

use DateTime as PhpDateTime;
use DateTimeZone;

class DateTime implements DateTimeInterface
{
    /** @var PhpDateTime */
    private $phpDateTime;

    /**
     * __construct
     *
     * @param  string       $datetime
     * @param  DateTimeZone $timezone
     * @return void
     */
    public function __construct($datetime = "now", DateTimeZone $timezone = null)
    {
        if ($datetime === "now") {
            $datetime = microtime(true);
        }

        if (is_float($datetime + 0)) { // "+ 0" implicitly converts $time to a numeric value
            $this->setDateTime($datetime, $timezone);
        } else {
            $this->setDateTime(strtotime($datetime) . ".0000", $timezone);
        }
    }

    /**
     * Get instance for the current date and time.
     * Get instance cho ngày và giờ hiện tại.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @return static
     */
    public static function now(DateTimeZone $timezone = null)
    {
        return new static("now", $timezone);
    }

    /**
     * Create a instance for today.
     * Tạo một instance cho ngày hiện tại
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @return static
     */
    public static function today($timezone = null)
    {
        return static::now($timezone)->startOfDay();
    }

    /**
     * Create a instance for tomorrow.
     * Tạo một instance cho ngày mai.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @return static
     */
    public static function tomorrow($timezone = null)
    {
        return static::now($timezone)->addDay()->startOfDay();
    }

    /**
     * Create a instance for yesterday.
     * Tạo một instance cho ngày hôm qua.
     *
     * @param DateTimeZone|string|null $timezone
     *
     * @return static
     */
    public static function yesterday($timezone = null)
    {
        return static::now($timezone)->subDay()->startOfDay();
    }

    /**
     * Get a copy of the instance.
     * Tạo ra bản sao chép cho instance hiện tại.
     *
     * @return static
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * Set datetime.
     * Thiết lập ngày giờ.
     *
     * @param  string|float $datetime.
     * @param  DateTimeZone $timezone.
     * @return void
     */
    private function setDateTime($datetime = "now", DateTimeZone $timezone = null)
    {
        list($datetimeString, $microseconds) = explode('.', $datetime);
        $microsecondsString = date('Y-m-d H:i:s.', is_string($datetime) ? strtotime($datetimeString) : $datetimeString) . $microseconds;

        if (!$timezone) {
            $this->phpDateTime = new PhpDateTime($microsecondsString);
        } else {
            $this->phpDateTime = new PhpDateTime($microsecondsString, $timezone);
        }

        $this->microseconds = $datetime - (int)$datetime;
    }

    /**
     * Returns the difference between two DateTime objects represented.
     * Trả về sự khác biệt giữa 2 thể hiện của DateTime object.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return \DateInterval|false
     */
    public function diff(DateTime $compareDatetime)
    {
        return $this->phpDatetime()->diff($compareDatetime->phpDatetime());
    }

    /**
     * Returns the difference microseconds between two DateTime objects represented.
     * Trả về sự khác biệt về micro giây giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffInMicroseconds(DateTime $compareDatetime, $absolute = true)
    {
        $diff = $this->diff($compareDatetime);
        $value = (int) round(((((($diff->m || $diff->y ? $diff->days : $diff->d) * static::HOURS_PER_DAY) +
            $diff->h) * static::MINUTES_PER_HOUR +
            $diff->i) * static::SECONDS_PER_MINUTE +
            ($diff->f + $diff->s)) * static::MICROSECONDS_PER_SECOND);

        return $absolute || !$diff->invert ? $value : -$value;
    }

    /**
     * Returns the difference seconds between two DateTime objects represented.
     * Trả về sự khác biệt về giây giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffInSeconds(DateTime $compareDatetime)
    {
        return (int)(abs(strtotime($compareDatetime) - strtotime($this)));
    }

    /**
     * Returns the difference minute between two DateTime objects represented.
     * Trả về sự khác biệt về phút giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffMinutes(DateTime $compareDatetime)
    {
        return  $this->diffHours($compareDatetime) * 60 + $this->getInterval($compareDatetime)->minutes;
    }

    /**
     * Returns the difference hours between two DateTime objects represented.
     * Trả về sự khác biệt về giờ giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffHours(DateTime $compareDatetime)
    {
        $diffDays = $this->getInterval($compareDatetime)->totalDays;

        return $diffDays * 24 + $this->getInterval($compareDatetime)->hours;
    }

    /**
     * Returns the difference days between two DateTime objects represented.
     * Trả về sự khác biệt về ngày giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffDays(DateTime $compareDatetime)
    {
        return $this->diff($compareDatetime)->totalDays;
    }

    /**
     * Returns the difference months between two DateTime objects represented.
     * Trả về sự khác biệt về tháng giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffMonths(DateTime $compareDatetime)
    {
        return $this->diff($compareDatetime)->months;
    }

    /**
     * Returns the difference years between two DateTime objects represented.
     * Trả về sự khác biệt về năm giữa hai thể hiện của DateTime objects.
     *
     * @param  \Support\Helper\DateTime $compareDatetime — The date to compare to.
     * @return int
     */
    public function diffYears(DateTime $compareDatetime)
    {
        return $this->diff($compareDatetime)->years;
    }

    /**
     * Get the difference in quarters rounded down.
     * Nhận sự khác biệt trong các quý được làm tròn xuống.
     *
     * @param DateTimeInterface|string|null $date
     * @param bool                           $absolute Get the absolute of the difference
     *
     * @return int
     */
    public function diffQuarters($date = null, $absolute = true)
    {
        return (int) ($this->diffMonths($date, $absolute) / static::MONTHS_PER_QUARTER);
    }

    /**
     * Get DateTime instance of php.
     * Trả về thể hiện của php Datetime object.
     *
     * @return \DateTime
     */
    public function phpDatetime()
    {
        if (!$this->phpDateTime) {
            $this->setDateTime();
        }

        return $this->phpDateTime;
    }

    /**
     * Get/set the day of year.
     * Lấy giá trị/set giá trị cho ngày trong năm
     *
     * @param int|null $value new value for day of year if using as setter.
     *
     * @return static|int
     */
    public function dayOfYear($value = null)
    {
        $dayOfYear = $this->format('z') + 1;
        if (!is_numeric($value)) {
            return $dayOfYear;
        }

        return $this->modifyCloneObject(sprintf("-%s days", $dayOfYear - $value));
    }

    /**
     * Modify clone object.
     * Chỉnh sửa từ một bản sao của chính nó.
     *
     * @param  string  $modifier
     * @return static
     */
    public function modifyCloneObject($modifier)
    {
        $cloneObject = $this->copy();

        $cloneObject->modify($modifier);

        return $cloneObject;
    }

    /**
     * @alias setTimezone
     *
     * @param DateTimeZone|string $value
     *
     * @return static
     */
    public function timezone($value)
    {
        return $this->setTimezone($value);
    }

    /**
     * Set the timezone or returns the timezone name if no arguments passed.
     * Thiết lập timezone hoặc trả về tên timezone nếu không truyền tham số.
     *
     * @param DateTimeZone|string $value
     *
     * @return static|string
     */
    public function tz($value = null)
    {
        if (func_num_args() < 1) {
            return $this->getTimezone();
        }

        if ($value instanceof DateTimeZone) {
            $this->setTimezone($value);
        } else {
            $this->setTimezone(new DateTimeZone($value));
        }

        return $this;
    }

    /**
     * Get/set the weekday from 0 (Sunday) to 6 (Saturday).
     * Lấy giá trị/ thiết lập giá trị của ngày trong tuần từ 0 (Chủ nhật) đến 6 (Thứ bảy).
     *
     * @param int|null $value new value for weekday if using as setter.
     *
     * @return static|int
     */
    public function weekday($value = null)
    {
        $dayOfWeek = $this->format('w') + 0;

        if (!is_numeric($value)) {
            return $dayOfWeek;
        }


        return $this->modifyCloneObject(sprintf("-%s days", $dayOfWeek - $value));
    }

    /**
     * Resets the time to 00:00:00.000000 start of day.
     * Thiết lập lại thời gian đến 00:00:00.000000 thời gian bắt đầu của ngày hôm đó.
     *
     * @return static
     */
    public function startOfDay()
    {
        return $this->setTime(0, 0, 0, 0);
    }

    /**
     * Resets the time to 23:59:59.999999 end of day.
     * Thiết lập lại thời gian đến 23:59:59.999999 thời gian kết thúc của ngày hôm đó.
     *
     * @return static
     */
    public function endOfDay()
    {
        return $this->setTime(static::HOURS_PER_DAY - 1, static::MINUTES_PER_HOUR - 1, static::SECONDS_PER_MINUTE - 1, static::MICROSECONDS_PER_SECOND - 1);
    }

    /**
     * Resets the date to the first day of the month and the time to 00:00:00.000000
     * Thiết lập lại thời gian đến ngày đầu tiên của tháng đó và thời gian đến 00:00:00.000000.
     *
     * @return static
     */
    public function startOfMonth()
    {
        $this->setDate($this->format("Y"), $this->format('n'), 1);

        return $this->startOfDay();
    }

    /**
     * Resets the date to end of the month and time to 23:59:59.999999
     * Thiết lập lại thời gian đến ngày cuối cùng của tháng đó và thời gian đến 23:59:59.999999.
     *
     * @return static
     */
    public function endOfMonth()
    {
        $this->setDate($this->format("Y"), $this->format('n'), $this->format('t'));

        return $this->endOfDay();
    }

    /**
     * Get the quarter.
     * Lấy quý hiện tại.
     *
     * @return int
     */
    public function quarter()
    {
        return ceil($this->format('n') / static::MONTHS_PER_QUARTER);
    }

    /**
     * Resets the date to the first day of the quarter and the time to 00:00:00.
     * Thiết lập lại thời gian đến ngày đầu tiên của quý đó và thời gian đến 00:00:00.000000.
     *
     * @return static
     */
    public function startOfQuarter()
    {
        $month = ($this->quarter() - 1) * static::MONTHS_PER_QUARTER + 1;

        $this->setDate($this->format('Y'), $month, 1);

        return $this->startOfDay();
    }

    /**
     * Resets the date to end of the quarter and time to 23:59:59.999999.
     * Thiết lập lại thời gian đến ngày cuối cùng của quý đó và thời gian đến 23:59:59.999999.
     *
     * @return static
     */
    public function endOfQuarter()
    {
        return $this->startOfQuarter()->addMonths(static::MONTHS_PER_QUARTER - 1)->endOfMonth();
    }

    /**
     * Resets the date to the first day of the year and the time to 00:00:00
     * Thiết lập lại thời gian đến ngày đầu tiên của năm đó và thời gian đến 00:00:00.000000.
     *
     * @return static
     */
    public function startOfYear()
    {
        $this->setDate($this->format("Y"), static::JANUARY, 1);

        return $this->startOfDay();
    }

    /**
     * Resets the date to end of the year and time to 23:59:59.999999.
     * Thiết lập lại thời gian đến ngày cuối cùng của năm đó và thời gian đến 23:59:59.999999.
     *
     * @return static
     */
    public function endOfYear()
    {
        $this->setDate($this->format("Y"), static::DECEMBER, 31);

        return$this->endOfDay();
    }

    /**
     * Add a second.
     *
     * @return static
     */
    public function addSecond()
    {
        return $this->addSeconds(1);
    }

    /**
     * Add seconds.
     *
     * @param  int  $value
     * @return static
     */
    public function addSeconds($value)
    {
        $this->modify("+ $value seconds");

        return $this;
    }

    /**
     * Add a minute.
     *
     * @return static
     */
    public function addMinute()
    {
        return $this->addMinutes(1);
    }

    /**
     * Add minutes.
     *
     * @param  int  $value
     * @return static
     */
    public function addMinutes($value)
    {
        $this->modify("+ $value minutes");

        return $this;
    }

    /**
     * Add a hour.
     *
     * @return static
     */
    public function addHour()
    {
        return $this->addHours(1);
    }

    /**
     * Add hours.
     *
     * @param  int  $value
     * @return static
     */
    public function addHours($value)
    {
        $this->modify("+ $value hours");

        return $this;
    }

    /**
     * Add a day.
     *
     * @return static
     */
    public function addDay()
    {
        return $this->addDays(1);
    }

    /**
     * Add days.
     *
     * @param  int  $value
     * @return static
     */
    public function addDays($value)
    {
        $this->modify("+ $value days");

        return $this;
    }

    /**
     * Add a month.
     *
     * @return static
     */
    public function addMonth()
    {
        return $this->addMonths(1);
    }

    /**
     * Add months.
     *
     * @param  int  $value
     * @return static
     */
    public function addMonths($value)
    {
        $this->modify("+ $value months");

        return $this;
    }

    /**
     * Add a year.
     *
     * @return static
     */
    public function addYear()
    {
        return $this->addYears(1);
    }


    /**
     * Add years.
     *
     * @param  int  $value
     * @return static
     */
    public function addYears($value)
    {
        $this->modify("+ $value years");

        return $this;
    }

    /**
     * Sub one day.
     *
     * @return static
     */
    public function subDay()
    {
        return $this->subDays(1);
    }

    /**
     * Sub days.
     *
     * @param  int  $value
     * @return static
     */
    public function subDays($value)
    {
        $this->modify("- $value days");

        return $this;
    }

    /**
     * Sub one month.
     *
     * @return static
     */
    public function subMonth()
    {
        return $this->subMonths(1);
    }

    /**
     * Sub months.
     *
     * @param  int  $value
     *
     * @return static
     */
    public function subMonths($value)
    {
        $this->modify("- $value months");

        return $this;
    }

    /**
     * Sub one year.
     *
     * @return static
     */
    public function subYear()
    {
        return $this->subYears(1);
    }

    /**
     * Sub years.
     *
     * @param  int  $value
     *
     * @return static
     */
    public function subYears($value)
    {
        $this->modify("- $value years");

        return $this;
    }

    /**
     * Sets the time
     *
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $microsecond
     * @return static|false
     */
    public function setTime($hour, $minute, $second = 0, $microsecond = 0)
    {
        $this->phpDateTime = new PhpDateTime(
            sprintf(
                "%s %d:%d:%d.%d",
                $this->format('Y-m-d '),
                $hour,
                $minute,
                @func_get_arg(2) !== false ? $second : $this->format('s'),
                @func_get_arg(3) !== false ? $microsecond : $this->format('u')
            )
        );

        return $this;
    }

    /**
     * Return timestamp.
     *
     * @return int|false
     */
    public function timestamp()
    {
        return strtotime($this);
    }

    /**
     *  Is run when writing data to inaccessible (protected or private) or non-existing properties.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->phpDateTime->{$name} = $value;
    }

    /**
     * Is utilized for reading data from inaccessible (protected or private) or non-existing properties.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this->phpDateTime, $name)) {
            return $this->phpDateTime->{$name};
        }
    }

    /**
     * Get datetime string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->phpDateTime->format('Y-m-d H:i:s.u');
    }

    /** @see __toString*/
    public function __invoke()
    {
        return $this->__toString();
    }

    /**
     * Is triggered when invoking inaccessible methods in an object context.
     *
     * @param  string $name
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (method_exists($datetime = $this->phpDatetime(), $method)) {
            return $datetime->$method(...$arguments);
        }

        throw new \BadFunctionCallException(sprintf("Call to undefined method %s:%s", self::class, $method));
    }

    /**
     * Is triggered when invoking inaccessible methods in a static context.
     *
     * @param  string $name
     * @param  mixed  $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return (new self)->$method(...$arguments);
    }
}
