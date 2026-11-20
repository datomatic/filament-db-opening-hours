<?php

declare(strict_types=1);

use Datomatic\DatabaseOpeningHours\Enums\Day;

return [

    /*
     * Show the "General" tab with a name field, useful when a model holds more
     * than one schedule (e.g. seasonal opening hours).
     */
    'general_description' => false,

    /*
     * Show a description field for each weekday.
     */
    'day_description' => false,

    /*
     * Show a description field for each time range.
     */
    'time_range_description' => false,

    /*
     * Show a description field for each date exception.
     */
    'exception_description' => false,

    /*
     * The weekday shown as the first tab of the form.
     */
    'first_day_of_week' => Day::MONDAY,

];
