<?php namespace TimeRange;

/**
 * Runs benchmarks for TimeRange class.
 * 
 * @package TimeRange
 * @author  Joonas Järnstedt <joonas@xnetti.net>
 */
class TestBenchmark extends \PHPUnit_Framework_TestCase
{

    /**
     * Benchmarking getDays function.
     */
    public function testGetDays()
    {
        $timerange = new TimeRange('1975-01-01', '2013-01-01');
        $timerange2 = new TimeRange('2012-01-01', '2012-01-01');

        $overlap_found = false;
        foreach ($timerange->getDays(1, TimeRange::FORWARD) as $date) {
            if ($timerange->overlaps($timerange2)) {
                $overlap_found = true;
            }
        }

        $this->assertTrue($overlap_found);
    }
}
