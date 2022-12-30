<?php

/**
 * @file
 * Contains \Drupal\alberora\Plugin\Field\FieldWidget\StatDefaultWidget.
 */

namespace Drupal\alberora\Plugin\Field\FieldWidget;

use Drupal\alberora\Database\StatDatabaseQueries;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Database\StatDatabase;

/**
 * Plugin implementation of the 'stat_default' widget.
 *
 * @FieldWidget(
 *   id = "stat_default",
 *   label = @Translation("Stat default"),
 *   field_types = {
 *     "stat_code"
 *   }
 * )
 */
class StatDefaultWidget extends WidgetBase
{
    /**
     * {@inheritdoc}
     */
    public function formElement(
        FieldItemListInterface $items,
                               $delta,
        array                  $element,
        array                  &$form,
        FormStateInterface     $form_state
    )
    {
        $element["stattype"] = [
            "#type" => "select",
            "#title" => $this->t("Stat type"),
            "#description" => $this->t("Select the type of stat"),
            "#options" => $this->getStatTypesOptions(),
            "#default_value" => $items[$delta]->stattype ?? -1,
        ];

        return $element;
    }

    /**
     * @return array
     */
    public static function getStatTypesOptions()
    {
        $options = [];

        $options[-1] = StatDatabaseQueries::EMPTY_OPTION;
        $options[1] = "Stat Admin";
        $options[2] = "Stat CSP";
        $options[3] = "Stat Dashboard";
        $options[4] = "Stat IP";
        $options[5] = "Stat IPs";
        $options[6] = "Stat Load";
        $options[7] = "Stat Login";
        $options[8] = "Stat Logins";
        $options[9] = "Stat Monitoring";
        $options[10] = "Stat Name";
        $options[11] = "Stat Pages";
        $options[12] = "Stat Parent";
        $options[13] = "Stat Statistics";
        $options[14] = "Stat UUID";
        $options[15] = "Stat UUIDs";
        $options[16] = "Stat Visitor";
        $options[17] = "Stat Visitors";

        return $options;
    }
}
