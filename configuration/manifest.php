<?php
$manifest['id'] = 'activitystreampurger';
$manifest['built_in_version'] = '8.0.0';
$manifest['name'] = 'CLI command and Scheduler job to purge from database old Activity Stream records';
$manifest['description'] = 'SugarActivityStreamPurger is a Sugar installable package containing both a CLI command and a Scheduler job to purge from database old Activity Stream records';
$manifest['author'] = 'Enrico Simonetti';
$manifest['acceptable_sugar_versions']['regex_matches'] = array('^8.[\d]+.[\d]+$', '^9.0.[\d]+$');
