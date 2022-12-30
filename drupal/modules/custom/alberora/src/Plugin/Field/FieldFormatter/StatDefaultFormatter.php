<?php

/**
 * @file
 * Contains \Drupal\alberora\Plugin\field\formatter\StatDefaultFormatter.
 */

namespace Drupal\alberora\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

use Drupal\alberora\Database\StatDatabase;

/**
 * Plugin implementation of the 'stat_default' formatter.
 *
 * @FieldFormatter(
 *   id = "stat_default",
 *   label = @Translation("Stat default"),
 *   field_types = {
 *     "stat_code"
 *   }
 * )
 */
class StatDefaultFormatter extends FormatterBase
{
    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode)
    {
        $elements = [];
        $source = [];
        foreach ($items as $delta => $item) {
            switch ($item->stattype) {
                case 1:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatAdminForm"
                        ),
                    ]);
                    break;
                case 2:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatCSPForm"
                        ),
                    ]);
                    break;
                case 3:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatDashboardForm"
                        ),
                    ]);
                    break;
                case 4:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatIPForm"
                        ),
                    ]);
                    break;
                case 5:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatIPsForm"
                        ),
                    ]);
                    break;
                case 6:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatLoadForm"
                        ),
                    ]);
                    break;
                case 7:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatLoginForm"
                        ),
                    ]);
                    break;
                case 8:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatLoginsForm"
                        ),
                    ]);
                    break;
                case 9:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatMonitoringForm"
                        ),
                    ]);
                    break;
                case 10:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatNameForm"
                        ),
                    ]);
                    break;
                case 11:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatPagesForm"
                        ),
                    ]);
                    break;
                case 12:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatParentForm"
                        ),
                    ]);
                    break;
                case 13:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatStatisticsForm"
                        ),
                    ]);
                    break;
                case 14:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatUUIDForm"
                        ),
                    ]);
                    break;
                case 15:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatUUIDsForm"
                        ),
                    ]);
                    break;
                case 16:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatVisitorForm"
                        ),
                    ]);
                    break;
                case 17:
                    array_push($source, [
                        "form" => \Drupal::formBuilder()->getForm(
                            "Drupal\alberora\Form\StatVisitorsForm"
                        ),
                    ]);
                    break;
                default:
                    array_push($source, [
                        "#markup" => "Please select a type of stat page",
                    ]);
            }

            $elements[$delta] = [
                "#markup" => \Drupal::service("renderer")->render($source),
            ];
        }

        return $elements;
    }
}
