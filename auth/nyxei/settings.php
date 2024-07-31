<?php


/**
 * Admin settings for plugin 'auth_nyxei'.
 *
 * @package   auth_nyxei
 * @copyright 2024 Nyx-EI {@link https://nyx-ei.tech}
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext(
        'auth_nyxei/host',
        get_string('host', 'auth_nyxei'),
        get_string('host_desc', 'auth_nyxei'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'auth_nyxei/login_attempts',
        get_string('login_attempts', 'auth_nyxei'),
        get_string('login_attempts_desc', 'auth_nyxei'),
        '',
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'auth_nyxei/bind_user',
        get_string('bind_user', 'auth_nyxei'),
        get_string('bind_user_desc', 'auth_nyxei'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'auth_nyxei/bind_password',
        get_string('bind_password', 'auth_nyxei'),
        get_string('bind_password_desc', 'auth_nyxei'),
        '',
    ));

    $settings->add(new admin_setting_configtext(
        'auth_nyxei/ad_group_role_mappings',
        get_string('ad_group_role_mappings', 'auth_nyxei'),
        get_string('ad_group_role_mappings_desc', 'auth_nyxei'),
        '',
        PARAM_TEXT
    ));
}