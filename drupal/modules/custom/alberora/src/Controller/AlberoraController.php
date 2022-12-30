<?php

namespace Drupal\alberora\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Alberora module.
 */
class AlberoraController extends ControllerBase
{
    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function databaseConfiguration()
    {
        return [
            "#markup" => "Configure database",
        ];
    }
}
