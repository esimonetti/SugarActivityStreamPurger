## WARNING
Take a full backup of the instance before installing the module. Once the module is installed and the Scheduler Job is activated, older Activity Streams records will be wiped from the database forever

After installing the module, activate the scheduler job "Activity Stream Record Purger Job" under Administrator -> Schedulers and make sure the Schedulers are running correctly for the instance

## Configuration
It is possible to configure if the system should keep the activities relevant to linking/unlinking of records forever or not, by leveraging the following Sugar config setting:
$sugar_config['activitystreamcleaner']['keep_all_relationships_activities'] = true;
The default value is true.

It is possible to configure the amount of records that will be deleted per Scheduler run, by leveraging the following Sugar config setting:
$sugar_config['activitystreamcleaner']['limit_scheduler_run'] = 25000;
The default value is 25000 records.

It is possible to configure the number of months to keep the activities records for, by leveraging the following Sugar config setting:
$sugar_config['activitystreamcleaner']['months_to_keep'] = 6;
The default value is to keep 6 months.

It is finally possible to configure if the system should execute the SQL count of records run-time, by leveraging the following Sugar config setting:
$sugar_config['activitystreamcleaner']['count_enabled'] = false;
The default value is false, as it might take a long time to execute.
