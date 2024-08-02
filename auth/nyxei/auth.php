<?php

// @todo ecrire un test pour la sysnchronisation des users
// @todo ecrire un test pour la gestion des permissions

/**
 * Active directory Authentification plugin
 * Authentification using LDAPS (Lightweight Directory Access Protocol)
 * This plugin uses the ldaps protocol for secure connection
 * Please make sure you have configured ldaps connections on your Active Directory server
 * 
 * @package   auth_nyxei
 * @author    NYX-EI <help@nyx-ei.tech>
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

// use core\context_system;
use auth_nyxei\NotificationMessages;

require_once($CFG->libdir .'/setuplib.php');
require_once($CFG->libdir . '/moodlelib.php');
// require_once($CFG->libdir . '/lib/contextlib.php');

class auth_plugin_nyxei extends auth_plugin_base
{

    const LDAP_PROTOCOL_VERSION = 3;
    const LDAP_PORT = 636;
    const LDAP_REFERRALS = 0;
    const LOGIN_ATTEMPTS =  3;

    public function __construct()
    {
        $this->authtype = 'nyxei';
        $this->config = get_config('auth_nyxei');
    }

    public function user_login($username, $password)
    {

        $ldap_host = $this->config->host;
        $ldap_port = self::LDAP_PORT;

        $ldap_connection = ldap_connect("ldaps://{$ldap_host}", $ldap_port);

        if (!$ldap_connection) {
            $this->failed_login_log($username, 'Could not connect to LDAP server');
            error_log('Could not connect to LDAP server.');
            return false;
        }

        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, self::LDAP_PROTOCOL_VERSION);
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, self::LDAP_REFERRALS);

        $ldap_bind = @ldap_bind($ldap_connection, $username, $password);

        if ($ldap_bind) {
            $this->close_ldap_connection($ldap_connection);
            return true;
        } else {
            $this->failed_login_log($username, 'Invalid Credentials');
            $this->close_ldap_connection($ldap_connection);
            return false;
        }
    }

    public function is_internal()
    {
        return false;
    }

    public function can_change_password()
    {
        return false;
    }

    public function config_form($config, $err, $user_fields)
    {
        include 'settings.php';
    }

    public function process_config($config)
    {
        if (empty($config->host)) {

            $config->host = '';
        }

        if (empty($config->login_attempts)) {

            $config->login_attempts = self::LOGIN_ATTEMPTS; // default value
        }

        if (empty($config->bind_user)) {

            $config->bind_user = '';
        }

        if (empty($config->bind_password)) {

            $config->bind_password = '';
        }

        if (empty($config->dc_base)) {

            $config->dc_base = '';
        }

        if (empty($config->dc_domain)) {

            $config->dc_domain = '';
        }

        set_config('host', $config->host, 'auth_nyxei');
        set_config('login_attempts', $config->login_attempts, 'auth_nyxei');
        set_config('bind_user', $config->bind_user, 'auth_nyxei');
        set_config('bind_password', $config->bind_password, 'auth_nyxei');
        set_config('dc_base', $config->dc_base, 'auth_nyxei');
        set_config('dc_domain', $config->dc_domain, 'auth_nyxei');

        return true;
    }

    //save attempts login
    private function failed_login_log($username, $error)
    {
        global $DB;

        $record = new stdClass();
        $record->username = $username;
        $record->timestamp = time();
        $record->error = $error;

        $DB->insert_record('auth_nyxei_failed_logins', $record);

        $this->check_failed_attempts($username);
    }

    private function check_failed_attempts($username)
    {
        global $DB, $CFG;

        $username = $this->sanitize_username($username);

        $attempts = $DB->get_records_select('auth_nyxei_failed_logins', 'username = :username', array('username' => $username));
        $attempt_count = count($attempts);

        if ($attempt_count >= $this->config->login_attempts) {

            $this->send_admin_notification($username, $attempt_count);
        }
    }

    private function send_admin_notification($username, $attempt_count)
    {
        global $CFG;

        $admin = get_admin();
        $subject = NotificationMessages::getMessage('alert_message_login_failed');
        $message = NotificationMessages::getMessage('failed_login_attempts_message', [
            $username,
            $attempt_count
        ]);

        email_to_user($admin, $admin, $subject, $message);
    }

    public function sync_users()
    {
        global $DB, $CFG;

        $ldap_host = $this->config->host;
        $ldap_port = self::LDAP_PORT;
        $bind_user = $this->config->bind_user;
        $bind_password = $this->config->bind_password;
        $dc_base = $this->config->dc_base;
        $dc_domian = $this->config->dc_domain;


        $ldap_connection = ldap_connect("ldaps://{$ldap_host}", $ldap_port);

        if (!$ldap_connection) {
            error_log('Could not connect to LDAP server.');
            return false;
        }

        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, self::LDAP_PROTOCOL_VERSION);
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, self::LDAP_REFERRALS);

        $ldap_bind = ldap_bind($ldap_connection, $bind_user, $bind_password);

        if (!$ldap_bind) {
            $error = ldap_error($ldap_connection);
            $this->close_ldap_connection($ldap_connection);
            error_log("Could not bind to LDAP server: $error.");
            return false;
        }

        $base_search = "$dc_base,$dc_domian";
        $search = ldap_search($ldap_connection, $base_search, "(objectClass=*)");
        $entries = ldap_get_entries($ldap_connection, $search);

        if ($entries === false) {
            $error = ldap_error($ldap_connection);
            $this->close_ldap_connection($ldap_connection);
            error_log("LDAP search failed: $error.");
            return false;
        }

        $ad_usernames = [];
        foreach ($entries as $entry) {
            if (!empty($entry['samaccountname'][0])) {
                $username = $entry['samaccountname'][0];
                $ad_usernames[] = $username;

                if (!$DB->record_exists('user', ['username' => $username])) {
                    $user = new stdClass();
                    $user->username = $username;
                    $user->firstname = $entry['givenname'][0] ?? '';
                    $user->lastname = $entry['sn'][0] ?? '';
                    $user->email = $entry['mail'][0] ?? '';
                    $user->auth = 'auth_nyxei';
                    $user->confirmed = 1;
                    $user->mnethostid = $CFG->mnet_localhost_id;

                    $DB->insert_record('user', $user);
                }

                if (isset($entry['useraccountcontrol'][0]) && ($entry['useraccountcontrol'][0] & 2)) {
                    $user = $DB->get_record('user', ['username' => $username]);
                    $user->suspended = 1;
                    $DB->update_record('user', $user);
                }
            }
        }

        $users = $DB->get_records('user', ['auth' => 'auth_nyxei']);
        foreach ($users as $user) {
            if (!in_array($user->username, $ad_usernames)) {
                $user->suspended = 1;
                $DB->update_record('user', $user);
            }
        }

        $this->close_ldap_connection($ldap_connection);
        return true;
    }

    public function sync_ad_groups_to_roles()
    {
        global $DB;

        
        $mappings = explode("\n", $this->config->ad_group_role_mappings);
        $mappings = array_filter(array_map('trim', $mappings));

        
        $ldap_connection = ldap_connect($this->config->host);
        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, self::LDAP_PROTOCOL_VERSION);
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, self::LDAP_REFERRALS);
        ldap_start_tls($ldap_connection);

        if (!ldap_bind($ldap_connection, $this->config->bind_user, $this->config->bind_password)) {
            throw new \moodle_exception('ldapbinderror', 'auth_nyxei');
        }

        if (empty($this->config->dc_base) || empty($this->config->dc_domain)) {
            throw new \moodle_exception('missingdcparameters', 'auth_nyxei');
        }

        $dc_base = $this->config->dc_base;
        $dc_domain = $this->config->dc_domain;

        foreach ($mappings as $mapping) {
            list($ad_group, $moodle_role) = explode(':', $mapping);

            $base_dn = "$dc_base,$dc_domain";
            $search_filter = "(memberOf=cn=$ad_group,$dc_domain)";

            $search = ldap_search($ldap_connection, $base_dn, $search_filter);
            $entries = ldap_get_entries($ldap_connection, $search);

            if ($entries['count'] > 0) {

                $roleid = $DB->get_field('role', 'id', ['shortname' => $moodle_role]);

                if ($roleid) {
                    foreach ($entries as $entry) {
                        if (isset($entry['samaccountname'][0])) {
                            $username = $entry['samaccountname'][0];
                            $user = $DB->get_record('user', ['username' => $username]);

                            if ($user) {

                                role_assign($roleid, $user->id, context_system::instance());
                            }
                        }
                    }
                } else {
                    error_log("The Moodle role '$moodle_role' does not exist.");
                }
            }
        }

        $this->close_ldap_connection($ldap_connection);
        return true;
    }


    /**
     * Closes the LDAP connection.
     * 
     * @param $ldap_connection
     * @return void
     */
    private function close_ldap_connection($ldap_connection)
    {
        ldap_unbind($ldap_connection);
    }

    private function sanitize_username($username)
    {
        $username = trim($username);
        return $username;
    }
}
