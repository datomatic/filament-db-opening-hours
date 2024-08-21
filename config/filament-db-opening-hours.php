<?php

// config for Datomatic/FilamentDatabaseOpeningHours
return [

	// text description to define an opening hours, used if you have multiple opening hours for a resource
	'general_description' => false,

	// text description to define a day opening hours
	'day_description' => false,

	// text description to define a time range hours
	'time_range_description' => false,

	// text description to define an exception
	'exception_description' => false,

	// first day of week
	'first_day_of_week' => \Datomatic\FilamentDatabaseOpeningHours\Enums\Day::MONDAY

];
