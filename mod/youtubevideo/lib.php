<?php

/**
 * @package youtubevideo
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

function youtubevideo_add_instance($data, $mform)
{
    global $DB;

    $data->timemodified = time();
    return $DB->insert_record('youtubevideo', $data);
}

function youtubevideo_update_instance($data, $mform)
{
    global $DB;
    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('youtubevideo', $data);
}

function youtubevideo_delete_instance($id)
{
    global $DB;

    if (!$youtubevideo = $DB->get_record('youtubevideo', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('youtubevideo', array('id' => $id));

    return true;
}