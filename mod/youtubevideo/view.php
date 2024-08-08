<?php

/**
 * @package youtubevideo
 * @copyright 2024 NYX-EI {@link http://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/youtubevideo/lib.php');
require_once($CFG->dirroot.'/mod/youtubevideo/form/youtubevideo_form.php');

//id of course
$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('youtubevideo', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);
require_login($course, true, $cm);

$video_url = optional_param('video_url', '', PARAM_URL);

if ($video_url)
{

    if (filter_var($video_url, FILTER_VALIDATE_URL) && strpos($video_url, 'youtube.com') !== false)
    {
        $video_id = parse_youtube_url($video_url);
        echo html_writer::start_tag(
            'iframe',
            array(
                'width' => '560',
                'height' => '315',
                'src' => "https://www.youtube.com/embed/$video_id",
                'frameborder' => '0',
                'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
                'allowfullscreen' => 'true'
            ),
        );
        echo html_writer::end_tag('iframe');
    } else {
        echo $OUTPUT->notification(get_string('invalid_url', 'youtubevideo'), 'notiferror');
    }

} else {
    $mform = new youtubevideo_form();

    if ($mform->is_cancelled())
    {
        echo $OUTPUT->notification(get_string('form_cancelled', 'youtubevideo'), 'notifinfo');
        redirect(new moodle_url('/course/view.php', array('id' => $course->id)), '', 3);
        exit();
    } elseif ($data = $mform->get_data()) {
        global $DB;

        $record = new stdClass();
        $record->course = $course->id;
        $record->video_url = $data->video_url;
        $record->timemodified = time();

        try {
            $DB->insert_record('youtubevideo', $record);
            redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
        } catch (Exception $e) {
            echo $OUTPUT->notification(get_string('insert_error', 'youtubevideo'), 'notiferror');
        }
    } else {
        echo $OUTPUT->heading(get_string('add_youtube_video', 'youtubevideo'));
        $mform->display();
    }

}
function parse_youtube_url($url)
{
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    return $params['v'];
}
