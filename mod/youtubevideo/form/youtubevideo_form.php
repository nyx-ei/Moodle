<?php

/**
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI  <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

require_once('../config.php');
require_once($CFG->libdir . '/formslib.php');

class youtubevideo_form extends moodleform {
    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('text', 'video_url', get_string('video_url', 'youtubevideo'));
        $mform->setType('video_url', PARAM_URL);
        $mform->addRule('video_url', null, 'required', null, 'client');

        $mform->addElement('submit', 'submitbutton', get_string('submit', 'youtubevideo'));
    }
}