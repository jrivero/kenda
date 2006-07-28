<?php
class timer
{
/**
* The start time of the timer
*/
var $starttime;

/**
* The end time of the timer
*/
var $endtime;


/**
* Constructor begins timer.
*/
function timer()
    {
    $this->starttime = microtime();
    }

/**
* Stops the timer.
*/
function timer_stop()
    {
    $this->endtime = microtime();
    }

/**
* Calculates and returns the time taken.
*
* Explodes the start and end time into arrays then totals up each array. Subtracts
* the start time from the end time to give time taken to the number of decimal places
* passed as a parameter.
*
* @param integer $decimals The number of decimal places to show in the timer. Default is 2.
* @return float The time taken between instantiating the class and stopping the timer.
*/
function timer_show($decimals=2)
    {
    // Make sure the parameter is an integer
    $decimals = intval($decimals);

    // Restrict decimal places to 8
    if($decimals > 8)
        {
        $decimals = 8;
        }
    // Decimal places cannot be below zero!
    if($decimals < 0)
        {
        $decimals = 0;
        }

    // Explode into arrays so that the decimal and UNIX timestamp can be added
    $starttime = explode(' ', $this->starttime);
    $endtime = explode(' ', $this->endtime);

    // Add the decimal to the timestamp
    $starttime = (float)$starttime[1] + (float)$starttime[0];
    $endtime = (float)$endtime[1] + (float)$endtime[0];

    return number_format($endtime - $starttime, $decimals);
    }

/**
* Stop and show the timer.
*
* Combines the stop and show timer functions.
*
* @param integer $decimals The number of decimal places to show in the timer. Default is 3.
* @return float The time taken between instantiating the class and stopping the timer.
*/
function timer_stop_show($decimals=3)
    {
    $this->timer_stop();
    return $this->timer_show($decimals);
    }
}
?>