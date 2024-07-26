<?php

declare(strict_types=1);

/**
 * AD connexion tests
 * 
 * @package auth_nyxei 
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech> 
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class auth_nyxei_testcase extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();
    }

    public function test_successful_ldap_connection()
    {
        set_config('host', 'ldap://your-ldap-server', 'auth_nyxei');
        set_config('bind_user', 'cn=admin,dc=example,dc=com', 'auth_nyxei');
        set_config('bind_password', 'password', 'auth_nyxei');

        $auth = new auth_plugin_nyxei();
        $username = 'testuser';
        $password = 'testpassword';

        
        $result = $auth->user_login($username, $password);
        $this->assertTrue($result);
    }

    public function test_failed_ldap_connection()
    {
        set_config('host', 'ldap://your-ldap-server', 'auth_nyxei');
        set_config('bind_user', 'cn=admin,dc=example,dc=com', 'auth_nyxei');
        set_config('bind_password', 'password', 'auth_nyxei');

        $auth = new auth_plugin_nyxei();
        $username = 'testuser';
        $password = 'testpassword';

        $result = $auth->user_login($username, $password);
        $this->assertFalse($result);
    }
}