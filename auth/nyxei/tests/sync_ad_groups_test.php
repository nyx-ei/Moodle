<?php

namespace auth_nyxei;

/**
 * Unit test for AD groups synchronization
 *
 * @package auth_nyxei
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

use auth_plugin_nyxei;
use PHPUnit\Framework\TestCase;

require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class sync_ad_groups_test extends \advanced_testcase
{
    protected function setUp(): void
    {
      $this->resetAfterTest();
      $this->resetConfigs();
    }

    protected function resetConfigs()
    {
        set_config('host', 'domaine.com', 'auth_nyxei');
        set_config('bind_user', 'cn=admin,dc=domaine,dc=com', 'auth_nyxei');
        set_config('bind_password', 'password', 'auth_nyxei');
        set_config('dc_base', 'dc=domaine,dc=com', 'auth_nyxei');
        set_config('dc_domain', 'dc=domaine,dc=com', 'auth_nyxei');
        set_config('ad_group_role_mappings', 'group1:role1' . "\n" . 'group2:role2', 'auth_nyxei');
    }

    public function test_sync_ad_groups_to_roles_success()
    {
        $this->mockLdapConnection();
        $this->mockLdapMethods();

        $auth = new auth_plugin_nyxei();

        $entries = [
            [
                'samaccountname' => ['testuser1'],
            ],
            [
                'samaccountname' => ['testuser2'],
            ],
        ];

        $this->mockLdapSearchResults($entries);

        $this->mockMoodleRole('role1');
        $this->mockMoodleRole('role2');

        $this->mockMoodleUser('testuser1');
        $this->mockMoodleUser('testuser2');

        $result = $auth->sync_ad_groups_to_roles();
        $this->assertTrue($result, 'La synchronisation des groupes AD avec les rôles devrait réussir.');

        $this->assertRoleAssigned('testuser1', 'role1');
        $this->assertRoleAssigned('testuser2', 'role2');
    }

    public function test_sync_ad_groups_to_roles_ldap_bind_fail()
    {
        $this->mockLdapBindFailure();

        $auth = new auth_plugin_nyxei();

        $this->expectException(\moodle_exception::class);
        $auth->sync_ad_groups_to_roles();
    }

    public function test_sync_ad_groups_to_roles_missing_dc_parameters()
    {
        set_config('dc_base', '', 'auth_nyxei');
        set_config('dc_domain', '', 'auth_nyxei');

        $auth = new auth_plugin_nyxei();

        $this->expectException(\moodle_exception::class);
        $auth->sync_ad_groups_to_roles();
    }

    public function test_sync_ad_groups_to_roles_role_not_exists()
    {
        $this->mockLdapConnection();
        $this->mockLdapMethods();

        $entries = [
            [
                'samaccountname' => ['testuser1'],
            ],
        ];

        $this->mockLdapSearchResults($entries);
        $this->mockMoodleRoleNotExists('role1');
        $this->mockMoodleUser('testuser1');

        $auth = new auth_plugin_nyxei();
        $result = $auth->sync_ad_groups_to_roles();
        $this->assertTrue($result, 'La synchronisation des groupes AD avec les rôles à réussir.');
    }

    protected function createMockLdapConnection()
    {
        $ldapConnection = $this->createMock(\stdClass::class);
        return $ldapConnection;
    }

    protected function mockLdapMethods($ldapConnection)
    {
        $ldapConnection->method('ldap_connect')
            ->willReturn($ldapConnection);

        $ldapConnection->method('ldap_bind')
            ->willReturn(true);

        $ldapConnection->method('ldap_search')
            ->willReturn(true);

        $ldapConnection->method('ldap_get_entries')
            ->willReturn($this->getLdapEntries());
    }

    protected function assertRoleAssigned($username, $role_shortname)
    {
        global $DB;
        $user = $DB->get_record('user', ['username' => $username]);
        $role = $DB->get_record('role', ['shortname' => $role_shortname]);
        $context = context_system::instance();
        $this->assertTrue(role_is_assigned($role->id, $user->id, $context));
    }

    protected function mockMoodleRoleNotExists($role_name)
    {
        global $DB;

        $this->getMockBuilder('stdClass')
            ->setMethods(['get_record'])
            ->getMock()
            ->expects($this->any())
            ->method('get_record')
            ->willReturnCallback(function($table, $conditions) use ($role_name) {
                if ($table == 'role' && $conditions['shortname'] == $role_name) {
                    return false;
                }
                return null;
            });
    }

    protected function mockMoodleUser($username)
    {
        global $DB;
        $user = new \stdClass();
        $user->id = $DB->insert_record('user', ['username' => $username, 'auth' => 'auth_nyxei']);
        return $user->id;
    }

    protected function mockLdapBindFailure($ldapConnection)
    {
        $ldapConnection->method('ldap_bind')
            ->willReturn(false);
    }

    protected function mockLdapSearchResults($entries)
    {
        $this->mockLdapMethods($this->createMockLdapConnection());
        $this->mockLdapConnection();

        $this->createMockLdapConnection()->method('ldap_get_entries')
            ->willReturn($entries);
    }


    protected function getLdapEntries()
    {
        return [
            [
                'samaccountname' => ['testuser1'],
            ],
            [
                'samaccountname' => ['testuser2'],
            ],
        ];
    }

}