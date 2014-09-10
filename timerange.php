<?php namespace TimeRange;

use InvalidArgumentException;
use DateTime;
use Exception;

/**
 * Compare and loop time ranges.
 *
 * @package TimeRange
 * @author  Joonas Järnstedt <joonas@xnetti.net>
 * @author  Juhani Viitanen <juhku@juhku.net>
 *
 */
class TimeRange implements \Iterator
{
    protected $start;
    protected $end;

    // Iterator
    private $position = 0;
    private $dates = array();

    const SECOND = 0;
    const MINUTE = 1;
    const HOUR = 2;
    const DAY = 3;
    const MONTH = 4;
    const YEAR = 5;

    const BACKWARD = 0;
    const FORWARD = 1;

    /**
     * Create TimeRange from DateTime objects or time strings.
     *
     * @param mixed $start DateTime object or datetime string
     * @param mixed $end DateTime object or datetime string
     * @throws InvalidArgumentException
     */
    public function __construct($start, $end)
    {
        try {

            if ($start === null or $end === null) {
                throw new InvalidArgumentException('Invalid DateTime.');
            }

            if (!$start instanceof DateTime) {
                // Create datetime from string
                $start = new Datetime($start);
            }

            if (!$end instanceof DateTime) {
                // Create datetime from string
                $end = new Datetime($end);
            }

            $this->start = clone $start;
            $this->end = clone $end;

        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid DateTime: ' . $e);
        }

        if ($this->start > $this->end) {
            throw new InvalidArgumentException(
                'TimeRange: The starting time must be before the ending time.'
            );
        }
    }

    /**
     * Change start datetime. Returns true if successful.
     *
     * @param mixed $start DateTime object or datetime string
     *
     * @throws InvalidArgumentException
     * @return bool
     */
    public function setStart($start)
    {
        try {
            if (!is_object($start)) {
                // Create datetime from string
                $start = new Datetime($start);
            }

            if ($start instanceof DateTime) {
                $this->start = clone $start;
            } else {
                throw new InvalidArgumentException('Invalid DateTime.');
            }

        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid DateTime.');
        }

        if ($this->start > $this->end) {
            throw new InvalidArgumentException(
                'TimeRange: The starting time must be before the ending time.'
            );
        }
        return true;
    }

    /**
     * Change end datetime. Returns true if successful.
     *
     * @param mixed $end DateTime object or datetime string
     *
     * @throws InvalidArgumentException
     * @return bool
     */
    public function setEnd($end)
    {
        try {
            if (!is_object($end)) {
                // Create datetime from string
                $end = new Datetime($end);
            }

            if ($end instanceof DateTime) {
                $this->end = clone $end;
            } else {
                throw new InvalidArgumentException('Invalid DateTime.');
            }

        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid DateTime.');
        }

        if ($this->start > $this->end) {
            throw new InvalidArgumentException(
                'TimeRange: The starting time must be before the ending time.'
            );
        }
        return true;
    }

    /**
     * Change start and end time.
     *
     * @param mixed $start DateTime object or datetime string
     * @param mixed $end DateTime object or datetime string
     *
     * @throws InvalidArgumentException
     * @return bool
     */
    public function setRange($start, $end)
    {
        if (!is_object($start)) {
            // Create datetime from string
            $start = new Datetime($start);
        }

        if ($start instanceof DateTime) {
            $this->start = clone $start;
        } else {
            throw new InvalidArgumentException('Invalid DateTime.');
        }

        if (!is_object($end)) {
            // Create datetime from string
            $end = new Datetime($end);
        }

        if ($end instanceof DateTime) {
            $this->end = clone $end;
        } else {
            throw new InvalidArgumentException('Invalid DateTime.');
        }

        if ($this->start > $this->end) {
            throw new InvalidArgumentException(
                'TimeRange: The starting time must be before the ending time.'
            );
        }
        return true;
    }

    /**
     * Returns true if the two given time ranges overlap.
     *
     * @param TimeRange|string $timeRange as object or string
     * @param int $precision SECOND/MINUTE/HOUR/DAY/MONTH/YEAR
     *
     * @throws InvalidArgumentException
     * @return bool
     */
    public function overlaps($timeRange, $precision = null)
    {
        switch ($precision) {
            case self::YEAR:
                $format = 'Y';
                break;
            case self::MONTH:
                $format = 'Ym';
                break;
            case self::DAY:
                $format = 'Ymd';
                break;
            case self::HOUR:
                $format = 'YmdH';
                break;
            case self::MINUTE:
                $format = 'YmdHi';
                break;
            default:
                // Compare precision is seconds (default)
                $format = 'YmdHis';
        }

        try {
            if ($timeRange instanceof TimeRange) {
                $startStr = $this->start->format($format);
                $endStr = $this->end->format($format);

                if ($startStr <= $timeRange->end->format($format) and
                    $timeRange->start->format($format) <= $endStr) {
                    return true;
                }
                return false;
            } elseif ($timeRange instanceof DateTime) {
                $date = $timeRange;
            } else {
                $date = new DateTime($timeRange);
            }

            if ($this->start->format($format) <= $date->format($format) and
                $this->end->format($format) >= $date->format($format)) {
                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid TimeRange: ' . $e);
        }
    }

    /**
     * Get array of minutes.
     *
     * @param int $interval Loop interval in minutes
     * @param int $direction FORWARD/BACKWARD
     *
     * @return array DateTime objects array
     */
    public function getMinutes($interval = 1, $direction = self::FORWARD)
    {
        $this->dates = array();

        if ($direction == self::FORWARD) {
            $iterator = clone $this->start;
            $hours = $this->start->format('H');
            $minutes = $this->start->format('i');
            $iterator->setTime($hours, $minutes, 0);

            while ($iterator <= $this->end) {

                $this->dates[] = clone $iterator;
                $iterator->modify("+$interval minute");
            }

        } else {
            $iterator = clone $this->end;
            
            while ($iterator >= $this->start) {
                $iterator->setTime(
                    $iterator->format('H'),
                    $iterator->format('i'),
                    0
                );
                $this->dates[] = clone $iterator;
                $iterator->setTime(
                    $iterator->format('H'),
                    $iterator->format('i'),
                    59
                );
                $iterator->modify("-$interval minute");
            }
        }

        return $this->dates;
    }

    /**
     * Get array of hours in the range.
     *
     * @param int $interval Loop interval in minutes
     * @param int $direction FORWARD/BACKWARD
     *
     * @return array DateTime objects array
     */
    public function getHours($interval = 1, $direction = self::FORWARD)
    {
        $this->dates = array();

        if ($direction == self::FORWARD) {
            $iterator = clone $this->start;
            $iterator->setTime($this->start->format('H'), 0, 0);

            while ($iterator <= $this->end) {

                $this->dates[] = clone $iterator;
                $iterator->modify("+$interval hour");
            }

        } else {
            $iterator = clone $this->end;
            
            while ($iterator >= $this->start) {
                $iterator->setTime($iterator->format('H'), 0, 0);
                $this->dates[] = clone $iterator;
                $iterator->setTime($iterator->format('H'), 59, 59);
                $iterator->modify("-$interval hour");
            }
        }

        return $this->dates;
    }

    /**
     * Get array of days in the range.
     *
     * @param int $interval Loop interval in days
     * @param int $direction FORWARD/BACKWARD
     *
     * @return array DateTime objects array
     */
    public function getDays($interval = 1, $direction = self::FORWARD)
    {
        $this->dates = array();

        $int = new \DateInterval("P{$interval}D");

        $start = clone $this->start;
        $start->setTime(0, 0, 0);
        $end = clone $this->end;
        // We use this to get the last day
        $end->setTime(12, 0, 0);
        $daterange = new \DatePeriod($start, $int, $end);

        foreach ($daterange as $date) {
            $this->dates[] = $date;
        }

        if ($direction == self::BACKWARD) {
            return array_reverse($this->dates);
        }

        return $this->dates;
    }

    /**
     * Get array of months in the range.
     *
     * @param int $interval Loop interval in months
     * @param int $direction FORWARD/BACKWARD
     *
     * @return array DateTime objects array
     */
    public function getMonths($interval = 1, $direction = self::FORWARD)
    {
        $dates = array();

        if ($direction == self::FORWARD) {
            $iterator = clone $this->start;
            $iterator->setTime(0, 0, 0);
            $year = $this->start->format('Y');
            $month = $this->start->format('m');
            $iterator->setDate($year, $month, 1);

            while ($iterator <= $this->end) {
                $dates[] = clone $iterator;
                $iterator->modify("+$interval month");
            }

        } else {
            $iterator = clone $this->end;
            $iterator->setDate($this->end->format('Y'), $this->end->format('m'), 1);
            $iterator->setTime(0, 0, 0);
            
            while ($iterator->format('Ym') >= $this->start->format('Ym')) {
                $dates[] = clone $iterator;
                $iterator->modify("-$interval month");
            }
        }

        return $dates;
    }

    /**
     * Get the start date of timerange.
     * 
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get the end date of timerange.
     * 
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Iteration function rewind.
     * 
     * @return void
     */
    public function rewind()
    {
        $this->getDays();
        $this->position = 0;
    }
    
    /**
     * Iteration function current.
     * 
     * @return DateTime
     */
    public function current()
    {
        return $this->dates[$this->position];
    }

    /**
     * Iteration function key.
     * 
     * @return int
     */
    public function key()
    {
        return $this->position;
    }
    
    /**
     * Iteration function next key.
     * 
     * @return int
     */
    public function next()
    {
        return ++$this->position;
    }

    /**
     * Iteration function validate key.
     * 
     * @return bool
     */
    public function valid()
    {
        return isset($this->dates[$this->position]);
    }
}
