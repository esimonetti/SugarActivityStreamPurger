<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

// post execute actions

require_once 'modules/Configurator/Configurator.php';

$configuratorObj = new Configurator();
$configuratorObj->loadConfig();
$configuratorObj->config['activitystreamcleaner']['keep_all_relationships_activities'] = true;
$configuratorObj->config['activitystreamcleaner']['months_to_keep'] = 6;
$configuratorObj->config['activitystreamcleaner']['limit_scheduler_run'] = 25000;
$configuratorObj->saveConfig();

$class = 'class::ActivityStreamPurgerJob';

$sugarQuery = new SugarQuery();
$sugarQuery->from(BeanFactory::getBean('Schedulers'));
$sugarQuery->select(array('id'));
$sugarQuery->where()->equals('job', $class);
$sugarQuery->limit(1);
$record = $sugarQuery->execute();

if (!empty($record) && !empty($record['0'])) {
    $scheduler = BeanFactory::getBean('Schedulers', $record['0']['id']);
} else {
    $scheduler = BeanFactory::newBean('Schedulers');
}

$scheduler->name = 'Activity Stream Record Purger Job';
$scheduler->job = $class;
$scheduler->date_time_start = '2000-01-01 00:00:01';
$scheduler->date_time_end = '2100-01-01 00:00:01';
$scheduler->job_interval = '*/15::*::*::*::*';
$scheduler->status = 'Inactive';
$scheduler->created_by = $current_user->id;
$scheduler->modified_user_id = $current_user->id;
$scheduler->catch_up = 0;
$scheduler->save();
