<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

namespace Sugarcrm\Sugarcrm\custom\Console\Command\ActivityStreamCleanup;

use Sugarcrm\Sugarcrm\Console\CommandRegistry\Mode\InstanceModeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Sugarcrm\Sugarcrm\custom\activitystream\ActivityStreamCleaner;

class ActivityStreamCleanupCommand extends Command implements InstanceModeInterface
{
    protected function activitystream()
    {
        if (empty($this->asc)) {
            $this->asc = new ActivityStreamCleaner();
        }
    
        return $this->asc;
    }

    protected function configure()
    {
        $this
            ->setName('activitystream:cleanup')
            ->setDescription('Hard delete Activity Stream records older than a time period in months');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start_time = microtime(true);

        $initial_count = $this->activitystream()->countRecords();
        $this->activitystream()->purgeSoftDeletedRecords();
        $this->activitystream()->purgeOldActivitiesRecords(false);
        $difference = $this->activitystream()->countRecordsDifference($initial_count);

        foreach ($difference as $table => $count) {
            $output->writeln('Purged ' . $count . ' records from table ' . $table  . ' (Activities Stream) in ' . round(microtime(true) - $start_time, 2) . ' seconds.');
        }
    }
}
