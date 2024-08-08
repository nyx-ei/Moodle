<?php

/**
 * test unit for add youtube in course
 *
 * @package youtubevideo
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/testlib.php');

class mod_youtubevideo_testcase extends advanced_testcase {

    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->course = $this->create_course();
        $this->cm = $this->create_module('youtubevideo', array('course' => $this->course->id));

        $this->context = context_module::instance($this->cm->id);
    }

    protected function tearDown(): void {
        global $DB;
        parent::tearDown();

        // Clean up the database
        $DB->delete_records('youtubevideo', array('course' => $this->course->id));
    }

    public function test_add_video() {
        global $DB;

        $video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $video_id = 'dQw4w9WgXcQ';

        $data = new stdClass();
        $data->course = $this->course->id;
        $data->video_url = $video_url;
        $data->timemodified = time();

        $record_id = $DB->insert_record('youtubevideo', $data);

        $videos = $DB->get_records('youtubevideo', array('course' => $this->course->id));
        $this->assertCount(1, $videos);
        $this->assertEquals($video_url, $videos[$record_id]->video_url);
        $this->assertEquals($video_id, parse_youtube_url($video_url));
    }

    public function test_parse_youtube_url() {
        $this->assertEquals('dQw4w9WgXcQ', parse_youtube_url('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertEquals('dQw4w9WgXcQ', parse_youtube_url('https://youtu.be/dQw4w9WgXcQ'));
    }

    public function test_invalid_video_url() {
        $this->expectException(InvalidArgumentException::class);
        parse_youtube_url('https://example.com/watch?v=dQw4w9WgXcQ');
    }
}