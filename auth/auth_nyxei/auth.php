<?php

//TODO: securisation de la conexion via AD en utilisant le protocole LDAPS
//TODO: Synchronisation des utilisateurs AD et Moodle
//TODO: Gestion des permissions d'utilisateur via AD
//TODO: Desactiver les autres methodes d'authentification afin que seule la connexion via AD soit possible.


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

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_nyxei extends auth_plugin_base {

    public function __construct() {
        $this->authtype = 'nyxei';
        $this->config = get_config('auth_nyxei');
    }

    public function user_login($username, $password) {

        $ldap_host = $this->config->host;
        $ldap_port = 636;

        $ldap_connection = ldap_connect("ldaps://{$ldap_host}", $ldap_port);

        if (!$ldap_connection) {
            
            error_log('Could not connect to LDAP server.');
            return false;
        }

        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);

        $ldap_bind = @ldap_bind($ldap_connection, $username, $password);

        if ($ldap_bind) {
            ldap_unbind($ldap_connection);
            return true;
        }else {
            ldap_unbind($ldap_connection);
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
        if (!isset($config->host)) {
            # code...
            $config->host = '';
        }

        set_config('host', $config->host, 'auth_nyxei');

        return true;
    }

}
