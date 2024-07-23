<?php

/**
 * auth_nyxei tasks
 * 
 * @package auth_nyxei
 * @author NYX-EI <help@nyx-ei.tech>
 * @copyright 2024 Nyx-EI {@link https://nyx-ei.tech}
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'auth_nyxei\task\sync_users',
        'blocking' => 0,
        'minute' => '*/30',
        'hour' => '*',
        'dayofmonth' => '*',
        'dayofweek' => '*',
        'month' => '*',
        'wday' => '*'
    )
);