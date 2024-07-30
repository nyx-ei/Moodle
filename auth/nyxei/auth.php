<?php

//TODO: Gestion des permissions d'utilisateur via AD

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



class auth_plugin_nyxei extends auth_plugin_base {

    const LDAP_PROTOCOL_VERSION = 3;
    const LDAP_PORT = 636;
    const LDAP_REFERRALS = 0;
    const LOGIN_ATTEMPTS =  3;

    public function __construct() {
        $this->authtype = 'nyxei';
        $this->config = get_config('auth_nyxei');
    }

    public function user_login($username, $password) {
        // global $DB;

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
        }else {
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

        set_config('host', $config->host, 'auth_nyxei');
        set_config('login_attempts', $config->login_attempts, 'auth_nyxei');
        set_config('bind_user', $config->bind_user, 'auth_nyxei');
        set_config('bind_password', $config->bind_password, 'auth_nyxei');

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

        $attempts = $DB->get_records_select('auth_nyxei_failed_logins', 'username = ?', array($username));
        $attempt_count = count($attempts);

        if ($attempt_count >= $this->config->login_attempts) {
             
            $this->send_admin_notification($username, $attempt_count);
        }
    }

    private function send_admin_notification($username, $attempt_count)
    {
        global $CFG;

        $admin = get_admin();
        $subject = "Alert: Multiple Failed Login Attempts";
        $message = "User {$username} has had {$attempt_count} failed login attempts";

        email_to_user($admin, $admin, $subject, $message);
    }

    public function sync_users() {
        global $DB, $CFG;
    
        $ldap_host = $this->config->host;
        $ldap_port = self::LDAP_PORT;
        $bind_user = $this->config->bind_user;
        $bind_password = $this->config->bind_password;
    
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
    
        $search = ldap_search($ldap_connection, "dc=nyx-ei,dc=tech", "(objectClass=*)");
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
    
    /**
     * Closes the LDAP connection.
     * 
     * @param $ldap_connection
     * @return void
     */
    private function close_ldap_connection($ldap_connection) {
        ldap_unbind($ldap_connection);
    }
    
}
