# SugarActivityStreamPurger

## Configuration
It is possible to configure the amount of records that will be deleted per scheduler run, by leveraging the following Sugar config setting:
`$sugar_config['activitystreamcleaner']['limit_scheduler_run'] = 50000;`

It is also possible to configure the number of months to keep the activities records for, by leveraging the following Sugar config setting:
`$sugar_config['activitystreamcleaner']['months_to_keep'] = 6;`

## Command Line
As the first cleanup might be lenghty, it might be a possibility to complete the initial cleanup via command line, by executing the following command:
`./bin/sugarcrm activitystream:cleanup`
The command above will delete all records at once, without pagination.

## Installation
* Clone this repository and enter the cloned directory
* Retrieve the Sugar Module Packager dependency by running: `composer install`
* Generate the installable .zip Sugar module with: `./vendor/bin/package <version number>`
* Take a full backup of the instance before installing the module. Once the module is installed and the Scheduler Job is activated, older Activity Streams records will be wiped from the database forever
* Install the generated module into the instance
* After installing the module, activate the scheduler job "Activity Stream Record Purger Job" under Administrator -> Schedulers and make sure the Schedulers are running correctly for the instance
