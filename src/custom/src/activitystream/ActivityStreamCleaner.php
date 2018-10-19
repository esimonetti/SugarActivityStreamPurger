<?php

// Enrico Simonetti
// enricosimonetti.com
// 2018-10-19 on 8.0.0 with MySQL

// $sugar_config['activitystreamcleaner']['months_to_keep'] = 6;
// $sugar_config['activitystreamcleaner']['limit_scheduler_run'] = 50000;

namespace Sugarcrm\Sugarcrm\custom\activitystream;
use Doctrine\DBAL\Connection;
use DBManagerFactory;

class ActivityStreamCleaner
{
    protected $tables = [
        'activities',
        'activities_users',
    ];

    protected $default_limit = 50000;
    protected $max_in_condition_limit = 500;
    protected $default_months_to_keep = 6;
    
    protected function getFilterDate() {
        // retrieve months to keep from config, or set default
        $months_to_keep = (int)\SugarConfig::getInstance()->get('activitystreamcleaner.months_to_keep', $this->default_months_to_keep);

        if ($months_to_keep == 0) {
            $months_to_keep = $this->default_months_to_keep;
        } else if ($months_to_keep < 0) {
            // if it is -1, process up to now
            $months_to_keep = 0;
        }

        return gmdate('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m') - $months_to_keep, date('d'), date('Y')));
    }

    protected function db() {
        return DBManagerFactory::getInstance();
    }

    public function purgeSoftDeletedRecords() {

        // delete all soft deletes

        $qb = $this->db()->getConnection()->createQueryBuilder();
        $qb->delete('activities');
        $qb->where('deleted = ' . $qb->createPositionalParameter(1));
        $GLOBALS['log']->info(__METHOD__ . ' ' . $qb->getSQL().' '.print_r($qb->getParameters(), true));
        $qb->execute();

        $qb = $this->db()->getConnection()->createQueryBuilder();
        $qb->delete('activities_users');
        $qb->where('deleted = ' . $qb->createPositionalParameter(1));
        $GLOBALS['log']->info(__METHOD__ . ' ' . $qb->getSQL().' '.print_r($qb->getParameters(), true));
        $qb->execute();

        $qb = $this->db()->getConnection()->createQueryBuilder();
        $qb->delete('comments');
        $qb->where('deleted = ' . $qb->createPositionalParameter(1));
        $GLOBALS['log']->info(__METHOD__ . ' ' . $qb->getSQL().' '.print_r($qb->getParameters(), true));
        $qb->execute();
    }

    public function countRecordsDifference($initial_count) {
        $final_count = [];
        if (!empty($initial_count)) {
            $current_count = $this->countRecords();
        
            foreach ($initial_count as $table => $value) {
                $final_count[$table] = $value - $current_count[$table];
            }
        }

        return $final_count;
    }

    public function countRecords() {

        // find all counts

        $results = [];
        foreach ($this->tables as $table) {
            $qb = $this->db()->getConnection()->createQueryBuilder();
            $qb->select('COUNT(id) as count');
            $qb->from($table);
            $res = $qb->execute();
            if ($row = $res->fetch()) {
                $results[$table] = $row['count'];
            }
        }

        return $results;
    }

    private function purgeActivitiesTableInIds($ids) {
        if (!empty($ids)) {
            $qb = $this->db()->getConnection()->createQueryBuilder();
            $qb->delete('activities');
            $qb->where(
                $qb->expr()->in(
                    'id',
                    $qb->createPositionalParameter((array) $ids, Connection::PARAM_STR_ARRAY)
                )
            );
            $GLOBALS['log']->info(__METHOD__ . ' ' . $qb->getSQL().' '.print_r($qb->getParameters(), true));
            $qb->execute();
        }
    }

    public function purgeOldActivitiesRecords($limited = true) {

        $limit = 0;
        $in_condition_limit = $this->max_in_condition_limit;

        if ($limited) {
            // retrieve limit from config
            $limit = (int)\SugarConfig::getInstance()->get('activitystreamcleaner.limit_scheduler_run', $this->default_limit);

            if ($limit <= 0) {
                $limit = 0;
            }

            // in condition limit
            $in_condition_limit = $this->max_in_condition_limit;
            if ($limit < $in_condition_limit) {
                $in_condition_limit = $limit;
            } 
        }

        $date_entered_keep = $this->getFilterDate();
        $GLOBALS['log']->info(__METHOD__ . ' Cleaning Activity Streams created before ' . $date_entered_keep . ' with query limit ' . $limit);

        // delete all non posts, that are old and that do not have an existing comment
        $qbSub = $this->db()->getConnection()->createQueryBuilder();
        $qbSub->select('parent_id');
        $qbSub->from('comments');

        $qb = $this->db()->getConnection()->createQueryBuilder();

        if ($limit > 0) {
            // if $limit > 0, proceed in $limit chunks and in $this->max_in_condition_limit subchunks
            $qb->select('id');
            $qb->from('activities');
        } else {
            // if zero, delete all
            $qb->delete('activities');
        }

        $qb->where('activity_type != ' . $qb->createPositionalParameter('post'));
        $qb->andWhere('date_entered < ' . $qb->createPositionalParameter($date_entered_keep));
        $qb->andWhere($qb->expr()->notIn('id', $qbSub->getSQL()));

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        $GLOBALS['log']->info(__METHOD__ . ' ' . $qb->getSQL().' '.print_r($qb->getParameters(), true));
        $res = $qb->execute();

        if ($limit > 0) {
            // if the limit is > 0 it means we need to execute the delete in chunks/subchunks
            $ids = [];

            while ($row = $res->fetch()) {
                $ids[] = $row['id'];

                if (count($ids) == $in_condition_limit) {
                    // complete one chunk
                    $this->purgeActivitiesTableInIds($ids);
                    $ids = [];
                }
            }

            // process last chunk if any
            $this->purgeActivitiesTableInIds($ids);
        }

        // delete all activities_users where id is not in activities (after the trimming)
        $qbSub = $this->db()->getConnection()->createQueryBuilder();
        $qbSub->select('id');
        $qbSub->from('activities');

        $qb = $this->db()->getConnection()->createQueryBuilder();
        $qb->delete('activities_users');
        $qb->where($qb->expr()->notIn('activity_id', $qbSub->getSQL()));
        $GLOBALS['log']->info(__METHOD__ . ' ' . $qb->getSQL().' '.print_r($qb->getParameters(), true));
        $qb->execute();
    }
}
