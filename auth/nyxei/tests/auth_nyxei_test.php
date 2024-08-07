<?php

namespace auth_nyxei;

/**
 * AD connexion tests
 * 
 * @package auth_nyxei 
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech> 
 */
defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

use auth_plugin_nyxei;
require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class auth_nyxei_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();
    }

    public function test_user_login_successful()
    {
        set_config('host', 'domaine.com', 'auth_nyxei');
        set_config('bind_user', 'cn=admin,dc=domaine,dc=com', 'auth_nyxei');
        set_config('bind_password', 'password', 'auth_nyxei');

        $auth = new auth_plugin_nyxei();
        $username = 'testuser';
        $password = 'testpassword';

        
        $result = $auth->user_login($username, $password);
        $this->assertTrue($result);
    }

    public function test_user_login_failed()
    {
        set_config('host', 'domaine.com', 'auth_nyxei');
        set_config('bind_user', 'cn=admin,dc=domaine,dc=com', 'auth_nyxei');
        set_config('bind_password', 'password', 'auth_nyxei');

        $auth = new auth_plugin_nyxei();
        $username = 'testuser';
        $password = 'testpassword';

        $result = $auth->user_login($username, $password);
        $this->assertFalse($result);
    }
}