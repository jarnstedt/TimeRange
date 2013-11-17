<?php
/**
 * Compare and loop time ranges.
 *
 * @package     TimeRange
 * @author      Joonas Järnstedt
 * @version     0.3
 *
 */
class TimeRange implements \Iterator
{
    
    private $start;
    private $end;

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
     */
    public function __construct($start, $end)
    {

        $this->position = 0;

        try {

            if (is_null($start) or is_null($end)) {
                throw new \InvalidArgumentException('Invalid DateTime.');
            }

            if (!is_object($start)) {
                // Create datetime from string
                $start = new \Datetime($start);
            }

            if (!is_object($end)) {
                // Create datetime from string
                $end = new \Datetime($end);
            }

            if ($start instanceof \DateTime and $end instanceof \DateTime) {
                $this->start = clone $start;
                $this->end = clone $end;
            } else {
                throw new \InvalidArgumentException('Invalid DateTime.');
            }

        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid DateTime.');
        }

        if ($this->start > $this->end) {
            throw new \InvalidArgumentException(
                'Invalid TimeRange. The starting time must be before the ending time.');
        }
    }

    /**
     * Change start datetime. Returns true if successful.
     */
    public function setStart($start)
    {
        try {
            if (!is_object($start)) {
                // Create datetime from string
                $start = new \Datetime($start);
            }

            if ($start instanceof \DateTime) {
                $this->start = clone $start;
            } else {
                throw new \InvalidArgumentException('Invalid DateTime.');
            }

        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid DateTime.');
        }

        if ($this->start > $this->end) {
            // 'Invalid TimeRange. The starting time must be before the ending time.
            return false;
        }
        return true;
    }

    /**
     * Change end datetime. Returns true if successful.
     */
    public function setEnd($end)
    {
        try {
            if (!is_object($end)) {
                // Create datetime from string
                $end = new \Datetime($end);
            }

            if ($end instanceof \DateTime) {
                $this->end = clone $end;
            } else {
                throw new \InvalidArgumentException('Invalid DateTime.');
            }

        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid DateTime.');
        }

        if ($this->start > $this->end) {
            // Invalid TimeRange. The starting time must be before the ending time.
            return false;
        }
        return true;
    }

    /**
     * Returns true if the two given time ranges overlap.
     * @param mixed $timeRange as object or string
     * @param const $precision SECOND/MINUTE/HOUR/DAY/MONTH/YEAR
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
                if ($this->start->format($format) <= $timeRange->end->format($format) and
                    $timeRange->start->format($format) <= $this->end->format($format))
                {
                    return true;
                }
                return false;
            }

            $timeRange = new \DateTime($timeRange);

            if ($this->start->format($format) <= $timeRange->format($format) and
                $this->end->format($format) >= $timeRange->format($format))
            {
                return true;
            } else {
                return false;
            }

        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid TimeRange: ' . $e);
        }
    }

    /**
     * Get array of minutes.
     * @param int $interval 
     * @param FORWARD/BACKWARD $direction 
     * @return DateTime array
     */
    public function getMinutes($interval = 1, $direction = self::FORWARD)
    {

        $this->dates = array();

        if ($direction == self::FORWARD) {
            $iterator = clone $this->start;
            $iterator->setTime($this->start->format('H'), $this->start->format('i'), 0);

            while ($iterator <= $this->end) {

                $this->dates[] = clone $iterator;
                $iterator->modify("+$interval minute");
            }

        } else {
            $iterator = clone $this->end;
            
            while ($iterator >= $this->start) {
                $iterator->setTime($iterator->format('H'), $iterator->format('i'), 0);
                $this->dates[] = clone $iterator;
                $iterator->setTime($iterator->format('H'), $iterator->format('i'), 59);
                $iterator->modify("-$interval minute");
            }
        }

        return $this->dates;
    }

    /**
     * Get array of hours in the range.
     * @param int $interval 
     * @param FORWARD/BACKWARD $direction 
     * @return DateTime array
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
     * @param int $interval 
     * @param FORWARD/BACKWARD $direction 
     * @return DateTime array
     */
    public function getDays($interval = 1, $direction = self::FORWARD)
    {

        $this->dates = array();

        if ($direction == self::FORWARD) {
            $iterator = clone $this->start;
            $iterator->setTime(0, 0, 0);

            while ($iterator <= $this->end) {

                $this->dates[] = clone $iterator;
                $iterator->modify("+$interval day");
            }

        } else {
            $iterator = clone $this->end;
            
            while ($iterator >= $this->start) {
                $iterator->setTime(0, 0, 0);
                $this->dates[] = clone $iterator;
                $iterator->setTime(23, 59, 59);
                $iterator->modify("-$interval day");
            }
        }

        return $this->dates;
    }

    /**
     * Get array of months in the range.
     * @param type $interval 
     * @param type $direction 
     * @return type
     */
    public function getMonths($interval = 1, $direction = self::FORWARD)
    {

        $dates = array();

        if ($direction == self::FORWARD) {
            $iterator = clone $this->start;
            $iterator->setTime(0, 0, 0);
            $iterator->setDate($this->start->format('Y'), $this->start->format('m'), 1);

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
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get the end date of timerange.
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Iteration functions.
     */
    public function rewind()
    {
        $this->getDays();
        $this->position = 0;
    }
    
    public function current()
    {
        return $this->dates[$this->position];
    }

    public function key()
    {
        return $this->position;
    }
    
    public function next()
    {
        return ++$this->position;
    }

    public function valid()
    {
        return isset($this->dates[$this->position]);
    }
}
