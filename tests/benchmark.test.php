<?php

class TestBenchmark extends PHPUnit_Framework_TestCase
{

    /**
     * Benchmarking getDays function.
     */
    public function testGetDays()
    {
        $timerange = new TimeRange('1975-01-01', '2013-01-01');
        $timerange2 = new TimeRange('2012-01-01', '2012-01-01');

        $overlap_found = false;
        foreach ($timerange->getDays() as $date) {
            if ($timerange->overlaps($timerange2)) {
                $overlap_found = true;
            }
        }

        $this->assertTrue($overlap_found);
        // $timerange = new TimeRange('2000-01-01 20:01:10', '2013-01-01 23:59:59');
        // $timerange->getDays();

        // $start = new \DateTime('1990-01-01');
        // $end = new \DateTime('2013-01-01');

        // $timerange = new TimeRange($start, $end);
        // $timerange->getDays();  

    }
}