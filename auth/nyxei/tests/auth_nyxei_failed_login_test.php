<?php

declare(strict_types=1);

// @todo ammeliorer les noms des methodes pour les tets

/**
 * Failed connection test
 * 
 * @package auth_nyxei
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 * @author NYX-EI <help@nyx-ei.tech>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/nyxei/auth.php');

class auth_nyxei_failed_login_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();
    }
}