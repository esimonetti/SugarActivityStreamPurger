<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

namespace Sugarcrm\Sugarcrm\custom\Console\Command\ActivityStreamCleanup;

use Sugarcrm\Sugarcrm\Console\CommandRegistry\Mode\InstanceModeInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption(
                'prevent-count',
                null,
                InputOption::VALUE_NONE,
                'Prevent record count'
            )
            ->setDescription('Hard delete Activity Stream records older than a time period in months. Use the option --prevent-count to prevent the execution of record count');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start_time = microtime(true);

        // force count or not
        $forceCount = true;
        if ($input->getOption('prevent-count') !== false) {
            $forceCount = false;
        }

        $initial_count = $this->activitystream()->countRecords($forceCount);
        $this->activitystream()->purgeSoftDeletedRecords();
        $this->activitystream()->purgeOldActivitiesRecords(false);
        $difference = $this->activitystream()->countRecordsDifference($initial_count, $forceCount);

        $output->writeln('Activity Stream Purge command executed successfully in ' . round(microtime(true) - $start_time, 2) . ' seconds');

        if ($this->activitystream()->isCountEnabled($forceCount) && !empty($difference)) {
            foreach ($difference as $table => $count) {
                if (!empty($initial_count[$table])) {
                    $output->writeln('The initial record count for database table ' . $table . ' was ' . $initial_count[$table]);
                }
                if (!empty($count)) {
                    $output->writeln('Purged ' . $count . ' records from database table ' . $table);
                }
            }
        }
    }
}
