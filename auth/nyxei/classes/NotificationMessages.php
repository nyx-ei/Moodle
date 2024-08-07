<?php

/**
 * @package   auth_nyxei
 * @author    NYX-EI <help@nyx-ei.tech>
 * @copyright 2024 NYX-EI {@link https://nyx-ei.tech}
 */
declare(strict_types=1);

namespace auth_nyxei;

defined('MOODLE_INTERNAL') || die();
class NotificationMessages
{
    private static $message = [
        'alert_message_login_failed' => "Alert: Multiple Failed Login Attempts",
        'failed_login_attempts_message' => "User %s has had %d failed login attempts"
    ];

    public static function getMessage(string $key,array $params = []): string
    {
        if (!isset(self::$message[$key])) {
            return 'Message ' . $key . ' does not exist';
        }

        $message = self::$message[$key];

        $message = vsprintf($message, $params);

        return $message;
    }
}