<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

// Run with: ./bin/sugarcrm activitystream:cleanup

$commandregistry = Sugarcrm\Sugarcrm\Console\CommandRegistry\CommandRegistry::getInstance();
$commandregistry->addCommands(array(new Sugarcrm\Sugarcrm\custom\Console\Command\ActivityStreamCleanup\ActivityStreamCleanupCommand()));
