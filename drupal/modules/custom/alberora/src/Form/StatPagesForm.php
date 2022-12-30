<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Form\AbstractStatForm;
use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * StatPagesForm
 */
class StatPagesForm extends AbstractStatForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_stat_pages_form";
    }

    /**
     * {@inheritdoc}
     */
    public function getChartName(int $chartType): string
    {
        return match ($chartType) {
            1 => "StatTypesGraph",
            2 => "StatHumanGraph",
            3 => "StatCleanGraph",
            default => "StatTrafficGraph",
        };
    }

    /**
     * @return array
     */
    public static function getChart(int $chartType, int $chartPeriod, int $target): string
    {
        return match ($chartType) {
            1 => StatDatabase::getStatTypesChart($chartPeriod, $target),
            2 => StatDatabase::getStatHumanChart($chartPeriod, $target),
            3 => StatDatabase::getStatCleanChart($chartPeriod, $target),
            default => StatDatabase::getStatTrafficChart($chartPeriod, $target),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $visitor = $this->getDefaultQueryValue(Self::INPUT_GET_ID);
        $form_state->set("visitor", $visitor);
        $this->validateVisitor($form_state);

        $form["#theme"] = $this->getTheme();

        $target = $this->getDefaultSelectValue($form_state, "target");
        $form["targetSelect"] = $this->getSelect($form_state, "target");

        $chartType = $this->getDefaultSelectValue($form_state, "chartType");
        $form["chartTypeSelect"] = $this->getSelect($form_state, "chartType");

        $chartPeriod = $this->getDefaultSelectValue($form_state, "chartPeriod");
        $form["chartPeriodSelect"] = $this->getSelect(
            $form_state,
            "chartPeriod"
        );

        $form["chartActions"]["#type"] = "actions";
        $form["chartActions"]["submit"] = [
            "#type" => "submit",
            "#value" => $this->t("Select"),
            "#button_type" => "primary",
        ];

        $form["chart_pages"] = [
            "#type" => "markup",
            "#markup" =>
                '<div id="' . $this->getChartName($chartType) . '"></div>',
            "#children" => $this->getChart($chartType, $chartPeriod, $target),
        ];

        $persona = $this->getDefaultSelectValue($form_state, "persona");
        $form["personaSelect"] = $this->getSelect($form_state, "persona");

        $visit = $this->getDefaultSelectValue($form_state, "visit");
        $form["visitSelect"] = $this->getSelect($form_state, "visit");

        $traffic = $this->getDefaultSelectValue($form_state, "traffic");
        $form["trafficSelect"] = $this->getSelect($form_state, "traffic");

        $type = $this->getDefaultSelectValue($form_state, "type");
        $form["typeSelect"] = $this->getSelect($form_state, "type");

        $warning = $this->getDefaultSelectValue($form_state, "warning");
        $form["warningSelect"] = $this->getSelect($form_state, "warning");

        $form["actions"]["#type"] = "actions";
        $form["actions"]["submit"] = [
            "#type" => "submit",
            "#value" => $this->t("Filter"),
            "#button_type" => "primary",
        ];

        $form["table_pages"] = [
            "#type" => "table",
            "#header" => [
                $this->t("Target"),
                $this->t("Date"),
                $this->t("Visitor"),
                $this->t("Persona"),
                $this->t("Visit"),
                $this->t("Path"),
                $this->t("Parameters"),
                $this->t("Traffic"),
                $this->t("Type"),
                $this->t("Warning"),
            ],
            "#rows" => StatDatabase::getStatPagesRows(
                $target,
                $persona,
                $visit,
                $traffic,
                $type,
                $warning,
                $visitor
            ),
            "#empty" => $this->t("No data"),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $this->validateVisitor($form_state);
        $this->validateSelectNum($form_state, "chartPeriod");
        $this->validateSelectNum($form_state, "chartType");

        $this->validateSelectNum($form_state, "target");
        $this->validateSelectNum($form_state, "persona");
        $this->validateSelectNum($form_state, "visit");
        $this->validateSelectNum($form_state, "traffic");
        $this->validateSelectNum($form_state, "type");
        $this->validateSelectNum($form_state, "warning");
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->initaliseSelecttvalue($form_state, "chartPeriod");
        $this->initaliseSelecttvalue($form_state, "chartType");

        $this->initaliseSelecttvalue($form_state, "target");
        $this->initaliseSelecttvalue($form_state, "persona");
        $this->initaliseSelecttvalue($form_state, "visit");
        $this->initaliseSelecttvalue($form_state, "traffic");
        $this->initaliseSelecttvalue($form_state, "type");
        $this->initaliseSelecttvalue($form_state, "warning");

        parent::submitForm($form, $form_state);
    }
}
