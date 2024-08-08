<?php

/**
 * A scheduled task for AD groups roles sync.
 *
 * @package    auth_nyxei
 * @author     NYX-EI <help@nyx-ei.tech>
 * @copyright  2024 Nyx-EI <help@nyx-ei.tech>
 */

namespace auth_nyxei\task;

global $CFG;

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class sync_ad_groups extends \core\task\scheduled_task {

    public function get_name()
    {
        return get_string('sync_ad_groups', 'auth_nyxei');
    }

    public function execute() {
        $auth = get_auth_plugin('nyxei');
        $auth->sync_ad_groups_to_roles();
    }
}

