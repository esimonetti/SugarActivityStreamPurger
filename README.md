# SugarActivityStreamPurger

SugarActivityStreamPurger is a Sugar installable package containing both a CLI command and a Scheduler job to purge from database old Activity Stream records.

## Configuration
It is possible to configure if the system should keep the activities relevant to linking/unlinking of records forever or not, by leveraging the following Sugar config setting:
```
$sugar_config['activitystreamcleaner']['keep_all_relationships_activities'] = true;
```
The default value is true.<br />

It is possible to configure the amount of records that will be deleted per Scheduler run, by leveraging the following Sugar config setting:
```
$sugar_config['activitystreamcleaner']['limit_scheduler_run'] = 25000;
```
The default value is 25000 records.<br />

It is possible to configure the number of months to keep the activities records for, by leveraging the following Sugar config setting:
```
$sugar_config['activitystreamcleaner']['months_to_keep'] = 6;
```
The default value is to keep 6 months.<br />

It is finally possible to configure if the system should execute the SQL count of records run-time, by leveraging the following Sugar config setting:
```
$sugar_config['activitystreamcleaner']['count_enabled'] = false;
```
The default value is false, as it might take a long time to execute.<br />
When the value is set to false, the record count is not executed by the Scheduler, but it is still enforced on the CLI command execution, unless specifically prevented via the CLI. If this value is set to true, it will be executed regardless of the use of the CLI optional parameter.<br />


## Command Line
As the first cleanup might be lenghty, it might be a possibility to complete the initial cleanup via command line, by executing the following command:
```
./bin/sugarcrm activitystream:cleanup
```
The above command will delete all records at once, without pagination and therefore it might take some time to execute.<br />
If the sugar config value `count_enabled` is set to false, it is possible to prevent the command line from executing a SQL record count per table, by passing the parameter '--prevent-count'. Example:
```
./bin/sugarcrm activitystream:cleanup --prevent-count
```

## Installation
* Clone this repository and enter the cloned directory
* Retrieve the Sugar Module Packager dependency by running: `composer install`
* Generate the installable .zip Sugar module with: `./vendor/bin/package 1.2`
* Take a full backup of the instance before installing the module. Once the module is installed and the Scheduler Job is activated, older Activity Streams records will be wiped from the database forever
* Install the generated module into the instance
* After installing the module, activate the Scheduler job "Activity Stream Record Purger Job" under Administrator -> Schedulers and make sure the Schedulers are running correctly for the instance
