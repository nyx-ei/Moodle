<?php

namespace auth_nyxei;

/**
 * Unit test for user synchronization
 *
 * @package auth_nyxei
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

use auth_plugin_nyxei;

require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class sync_users_test extends \advanced_testcase
{
  protected function setUp(): void
  {
      $this->resetAfterTest();
  }
}
