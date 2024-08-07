<?php

namespace auth_nyxei;

/**
 * Unit test for user synchronization
 *
 * @package auth_nyxei
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

global $DB, $CFG;

use auth_plugin_nyxei;
use PHPUnit\Framework\TestCase;

require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class sync_users_test extends \advanced_testcase
{
  protected function setUp(): void
  {
      $this->resetAfterTest();
      $this->resetConfigs();
  }

  protected  function resetConfigs()
  {
      set_config('host', 'domaine.com', 'auth_nyxei');
      set_config('bind_user', 'cn=admin,dc=domaine,dc=com', 'auth_nyxei');
      set_config('bind_password', 'password', 'auth_nyxei');
      set_config('dc_base', 'dc=domaine,dc=com', 'auth_nyxei');
      set_config('dc_domain', 'dc=domaine,dc=com', 'auth_nyxei');
  }

  protected function createMockLdapConnection()
  {
      return $this->createMock(\stdClass::class);
  }

  protected  function mockLdapMethods($ldap_connection)
  {
    $ldap_connection->method('ldap_connect')->willReturn($ldap_connection);
    $ldap_connection->method('ldap_pbind')->willReturn(true);
    $ldap_connection->method('ldap_search')->willReturn(true);
    $ldap_connection->method('ldap_get_entries')->willReturn($this->getLdapEntries());
  }

  public function test_sync_users_successfull()
  {
      $ldap_connection = $this->createMockLdapConnection();
      $this->mockLdapMethods($ldap_connection);

      $auth = new auth_plugin_nyxei();

      $entries = [
          [
              'samaccountname' => ['testuser1'],
              'givenname' => ['tamo'],
              'sn' => ['giress'],
              'mail' => ['tamogiress@example.com'],
              'useraccountcontrol' => [512],
          ],
          [
              'samaccountname' => ['testuser2'],
              'givenname' => ['fulbert'],
              'sn' => ['tala'],
              'mail' => ['fulberttala@example.com'],
              'useraccountcontrol' => [514],
          ],
      ];

      $this->mockLdapSearchResults($entries);

      $results = $auth->sync_users();

      $this->assertTrue($results, 'La synchromisation des utilisateurs');

      $this->assertTrue($DB->record_exists('user', ['username' => 'testuser1']));
      $this->assertTrue($DB->record_exists('user', ['username' => 'testuser2']));

      $user1 = $DB->get_record('user', ['username' => 'testuser1']);
      $this->assertEquals(0, $user1->suspended);

      $user2 = $DB->get_record('user', ['username' => 'testuser2']);
      $this->assertEquals(1, $user2->suspended);
  }

  public function test_sync_users_ldap_connection_fail()
  {
      $auth = new auth_plugin_nyxei();

      $this->expectException(\Exception::class);
      $auth->sync_users();
  }

  public function test_sync_users_ldap_bind_fail()
  {
      $ldap_connection = $this->createMockLdapConnection();
      $this->mockLdapConnectionFailure($ldap_connection);

      $auth = new auth_plugin_nyxei();

      $this->expectException(\Exception::class);
      $auth->sync_users();
  }

  public function test_sync_users_ldap_search_fail()
  {
      $ldap_connection = $this->createMockLdapConnection();
      $this->mockLdapSearchFailure($ldap_connection);

      $auth = new auth_plugin_nyxei();

      $result = $auth->sync_users();
      $this->assertFalse($result, 'La synchronisation des utilisateurs à échouer.');
  }
  protected function mockLdapSearchResults($entries)
  {
      $this->mockLdapMethods($this->createMockLdapConnection());
      $this->mockLdapConnection();

      $this->createMockLdapConnection()->method('ldap_get_entries')
          ->willReturn($entries);
  }

  protected function mockLdapConnectionFailure($ldap_connection)
  {
      $ldap_connection->method('ldap_bind')
          ->willReturn(false);
  }

  protected function mockLdapSearchFailure($ldap_connection)
  {
      $ldap_connection->method('ldap_search')
          ->willReturn(false);
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
