<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

// post uninstall actions

require_once 'modules/Configurator/Configurator.php';

$configuratorObj = new Configurator();
$configuratorObj->loadConfig();
$configuratorObj->config['activitystreamcleaner'] = false;
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
    $scheduler->mark_deleted($record['0']['id']);
}
