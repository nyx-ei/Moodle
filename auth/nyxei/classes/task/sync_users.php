<?php


/**
 * A scheduled task for LDAP users sync.
 *
 * @package    auth_nyxei
 * @author     NYX-EI <help@nyx-ei.tech>
 * @copyright  2024 Nyx-EI <help@nyx-ei.tech>
 */

namespace auth_nyxei\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class sync_users extends \core\task\scheduled_task {
    public function get_name()
    {
        return get_string('sync_users', 'auth_nyxei');
    }

    public function execute() 
    {
        $auth  = get_auth_plugin('nyxei');
        $auth->sync_users();
    }
}