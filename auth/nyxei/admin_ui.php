<?php

/**
 * User interface for mappings
 * 
 * @package auth_nyxei
 * @copyright 2024 Nyx-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

require_once('../../config.php');

require_login();
admin_externalpage_setup('auth_nyxei_settings');

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('nopermissions', 'error', '', 'access this page');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ad_group_role_mappings'])) {
    $mappings = trim($_POST['ad_group_role_mappings']);
    set_config('ad_group_role_mappings', $mappings, 'auth_nyxei');
    echo '<div class="alert alert-success">Mappings saved successfully!</div>';
}

echo $OUTPUT->header();
echo '<h2>' . get_string('ad_group_role_mappings', 'auth_nyxei') . '</h2>';

$current_mappings = get_config('auth_nyxei', 'ad_group_role_mappings');
echo '<form method="post">';
echo '<textarea name="ad_group_role_mappings" rows="10" cols="50">' . s($current_mappings) . '</textarea>';
echo '<br><br>';
echo '<input type="submit" value="' . get_string('savechanges') . '" class="btn btn-primary">';
echo '</form>';

// Affiche le pied de page
echo $OUTPUT->footer();
