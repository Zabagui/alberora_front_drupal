<?php

/**
 * @file
 * Contains \Drupal\alberora\Log.
 */

namespace Drupal\alberora\Log;

class StatLog
{
    const LOG_NAME = "alberora";

    public static function debug($message)
    {
        StatLog::log(" - DEBUG - " . $message);
    }

    public static function info($message)
    {
        StatLog::log(" -  INFO - " . $message);
    }

    public static function error($message)
    {
        StatLog::log(" - ERROR - " . $message);
    }

    private static function log($message)
    {
        \Drupal::logger(StatLog::LOG_NAME)->notice(
            sprintf("%08d", \Drupal::currentUser()->id()) . $message
        );
    }
}
