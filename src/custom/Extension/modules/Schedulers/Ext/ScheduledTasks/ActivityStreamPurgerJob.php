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

        $messages = '';
        foreach ($difference as $table => $count) {
            $messages .= 'Purged ' . $count . ' records from table ' . $table  . ' (Activities Stream) in ' . round(microtime(true) - $start_time, 2) . ' seconds.' . PHP_EOL;
        }
        $this->job->succeedJob($messages);
    }
}
