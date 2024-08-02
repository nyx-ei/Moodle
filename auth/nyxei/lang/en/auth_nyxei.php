<?php

/**
 * Strings for component 'auth_nyxei', language 'en'.
 *
 * @package   auth_nyxei
 * @copyright 2024 Nyx-EI {@link https://nyx-ei.tech}
 */

$string['pluginname'] = 'Active Directory Authentication';
$string['auth_nyxeidescription'] = 'This plugin allows authentication against an Active Directory server using LDAPS.';
$string['host'] = 'AD Host';
$string['host_desc'] = 'The hostname or IP address of the Active Directory server.';
$string['login_attempts'] = 'Number of login attempts';
$string['login_attempts_desc'] = 'Number of failed login attempts before sending an alert to the administrator.';
$string['bind_user'] = 'Bind User';
$string['bind_user_desc'] = 'The user to bind to the Active Directory server.';
$string['bind_password'] = 'Bind Password';
$string['bind_password_desc'] = 'The password to bind to the Active Directory server.';
$string['sync_users'] = 'Synchronize Users';
$string['sync_users_desc'] = 'Synchronize users from LDAP to moodle.';
$string['ad_group_role_mappings'] = 'Active directory Group to Moodle Role Mappings';
$string['ad_group_role_mappings_desc'] = 'Enter mappings in the format "AD Group Name:Moodle Role Shortname", one per line.';
$string['dc_base'] = 'Base DC';
$string['dc_base_desc'] = 'Enter the base DN for the LDAP search example: "domaine".';
$string['dc_domain'] = 'Domain DC';
$string['dc_domain_desc'] = 'Enter the domain DN for the LDAP search example: "com".';