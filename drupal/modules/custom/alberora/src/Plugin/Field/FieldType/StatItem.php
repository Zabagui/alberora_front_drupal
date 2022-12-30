<?php
/**
 * @file
 * Contains \Drupal\alberora\Plugin\Field\FieldType\StatItem.
 */

namespace Drupal\alberora\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'Stat Type' field type.
 *
 * @FieldType(
 *   id = "stat_code",
 *   label = @Translation("Stat Type field"),
 *   description = @Translation("This field stores the type of stat form."),
 *   category = @Translation("Alberora"),
 *   default_widget = "stat_default",
 *   default_formatter = "stat_default"
 * )
 */
class StatItem extends FieldItemBase
{
    /**
     * {@inheritdoc}
     */
    public static function schema(FieldStorageDefinitionInterface $field)
    {
        return [
            "columns" => [
                "stattype" => [
                    "type" => "int",
                    "not null" => 0,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        $personaValue = $this->get("stattype")->getValue();
        return $personaValue === null;
    }

    /**
     * {@inheritdoc}
     */
    static $propertyDefinitions;

    /**
     * {@inheritdoc}
     */
    public static function propertyDefinitions(
        FieldStorageDefinitionInterface $field_definition
    )
    {
        $properties["stattype"] = DataDefinition::create("integer")
            ->setLabel(t("stattype"))
            ->setDescription(t("Stat type selection"));

        return $properties;
    }
}
