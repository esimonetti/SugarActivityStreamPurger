<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

use Sugarcrm\Sugarcrm\custom\activitystream\ActivityStreamCleaner;

$job_strings[] = 'class::ActivityStreamPurgerJob';

class ActivityStreamPurgerJob implements \RunnableSchedulerJob
{
    protected $job;

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }

    public function run($data)
    {
        $start_time = microtime(true);

        $acs = new ActivityStreamCleaner();
        $initial_count = $acs->countRecords();
        $acs->purgeSoftDeletedRecords();
        $acs->purgeOldActivitiesRecords();
        $difference = $acs->countRecordsDifference($initial_count);

        $messages = 'Activity Stream Purge Job executed successfully in ' . round(microtime(true) - $start_time, 2) . ' seconds' . PHP_EOL;
        if ($acs->isCountEnabled() && !empty($difference)) {
            foreach ($difference as $table => $count) {
                if (!empty($initial_count[$table])) {
                    $messages .= 'The initial record count for database table ' . $table . ' was ' . $initial_count[$table] . PHP_EOL;
                }
                if (!empty($count)) {
                    $messages .= 'Purged ' . $count . ' records from database table ' . $table . PHP_EOL;
                }
            }
        }

        $this->job->succeedJob($messages);
    }
}
