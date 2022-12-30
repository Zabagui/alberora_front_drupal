<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * AbstractStatForm
 */
abstract class AbstractStatForm extends FormBase
{

    const DEFAULT_VALUE_INT = -1;
    const DEFAULT_VALUE_STRING = "";
    const INPUT_GET_ID = "id";

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_abstract_stat_form";
    }

    /**
     * @return string
     */
    protected function getTheme(): string
    {
        return $this->getFormId() . "_theme";
    }

    /**
     * @return string
     */
    protected function getDefaultSelectValue(
        FormStateInterface $form_state,
        string             $name
    ): int
    {
        return empty($form_state->get($name))
            ? Self::DEFAULT_VALUE_INT
            : $form_state->get($name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getDefaultQueryValue(string $name): string
    {
        return empty(filter_input(INPUT_GET, $name, FILTER_UNSAFE_RAW))
            ? Self::DEFAULT_VALUE_STRING
            : filter_input(INPUT_GET, $name, FILTER_UNSAFE_RAW);
    }

    /**
     * @param FormStateInterface $form_state
     * @param string $name
     * @param string $defaultValue
     * @return array
     */
    protected function getSelect(
        FormStateInterface $form_state,
        string             $name,
        string             $defaultValue = ""
    ): array
    {
        if (empty($defaultValue)) {
            $defaultValue = $this->getDefaultSelectValue($form_state, $name);
        }
        return [
            "#type" => "select",
            "#field_prefix" =>
                "<strong>" . $this->t("Select") . " " . $name . ": </strong>",
            "#options" => StatDatabase::getOptions($name),
            "#default_value" => $defaultValue,
            "#required" => false,
        ];
    }

    /**
     * @param FormStateInterface $form_state
     * @param string $name
     * @return void
     */
    protected function validateSelectNum(
        FormStateInterface $form_state,
        string             $name
    )
    {
        if (!is_numeric($form_state->getValue($name . "Select"))) {
            $form_state->setErrorByName(
                $name . "Select",
                $name . $this->t(" is incorrect.")
            );
        }
    }

    /**
     * @param FormStateInterface $form_state
     * @return void
     */
    protected function validateVisitor(FormStateInterface $form_state)
    {
        if (strlen($form_state->get("visitor")) > 32) {
            $form_state->setErrorByName(
                "visitor",
                $this->t("The visitor is incorrect.")
            );
        }
    }

    /**
     * @param FormStateInterface $form_state
     * @param string $name
     * @return void
     */
    protected function initaliseSelecttvalue(
        FormStateInterface $form_state,
        string             $name
    )
    {
        $form_state->set($name, $form_state->getValue($name . "Select"));
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        StatLog::info($this->getFormId() . ".submit");
        $form_state->setRebuild(true);
    }

    /**
     * @param $string
     * @param $start
     * @param $end
     * @return string
     */
    protected function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * {@inheritdoc}
     */
    public function submitFormFilterVisitors(
        array              &$form,
        FormStateInterface $form_state
    )
    {
        $this->initaliseSelecttvalue($form_state, "persona");
        $this->initaliseSelecttvalue($form_state, "human");
        $this->initaliseSelecttvalue($form_state, "browser");

        $this->submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitFormMergeVisitors(
        array              &$form,
        FormStateInterface $form_state
    )
    {
        $slectedVisitors = array_filter($form_state->getValues()['table_details']);
        $visitors = $form_state->get("visitors");
        $visitorsList = [];
        $parent = "";
        foreach ($slectedVisitors as $key) {
            $visitor = $this->getStringBetween($visitors[$key][0], "<small>", "</small>");
            $parent = $this->getStringBetween($visitors[$key][1], "<small>", "</small>");
            array_push($visitorsList, $visitor, $parent);
        }

        $visitorsList = array_unique($visitorsList);
        if ($parent != "") {
            StatDatabase::mergeVisitors($visitorsList, $parent);
        }

        $this->submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitFormApplyPersona(
        array              &$form,
        FormStateInterface $form_state
    )
    {
        $slectedVisitors = array_filter($form_state->getValues()['table_details']);
        $visitors = $form_state->get("visitors");
        foreach ($slectedVisitors as $key) {
            $visitor = $this->getStringBetween($visitors[$key][0], "<small>", "</small>");
            StatDatabase::saveStatVisitorPersona(
                $visitor,
                $form_state->getValue("personaSelect")
            );
        }

        $this->submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function DisplayDetailedForm(
        array              &$form,
        FormStateInterface $form_state,
        array              $record,
        string             $fieldName,
        string             $FieldValue,
        string             $login,
        string             $name,
        string             $uuid,
        string             $ip)
    {
        if (empty($record)) {
            $form["empty"] = [
                "#type" => "markup",
                "#markup" =>
                    "<strong>There is no " . $fieldName . " with this value (" . $FieldValue . ").</strong>",
            ];
        } else {
            $form["#theme"] = $this->getTheme();

            $form["login"] = [
                "#type" => "markup",
                "#markup" => "<strong>" . $fieldName . ": </strong>" . $FieldValue,
            ];

            $form["start_date"] = [
                "#type" => "markup",
                "#markup" => "<strong>From: </strong>" . date("d/m/y H:i:s", strtotime($record["START_DATE"])),
            ];

            $form["end_date"] = [
                "#type" => "markup",
                "#markup" => "<strong>To: </strong>" . date("d/m/y H:i:s", strtotime($record["END_DATE"])),
            ];

            $persona = $this->getDefaultSelectValue($form_state, "persona");
            $form["personaSelect"] = $this->getSelect($form_state, "persona");

            $human = $this->getDefaultSelectValue($form_state, "human");
            $form["humanSelect"] = $this->getSelect($form_state, "human");

            $browser = $this->getDefaultSelectValue($form_state, "browser");
            $form["browserSelect"] = $this->getSelect($form_state, "browser");

            $options = StatDatabase::getStatVisitorsDetailsOptions($login, $name, $uuid, $ip, $persona, $human, $browser);

            $form_state->set("visitors", $options);

            $form["table_details"] = [
                "#type" => "tableselect",
                '#title' => $this->t('Visitors'),
                "#header" => [
                    $this->t("Visitor"),
                    $this->t("Parent"),
                    $this->t("IPs"),
                    $this->t("Logins"),
                    $this->t("UUIDs"),
                    $this->t("Agent"),
                    $this->t("Characteristics"),
                    $this->t("Geolocalisation"),
                    $this->t("Last visit"),
                ],
                "#options" => $options,
                "#empty" => $this->t("No data"),
            ];

            $form["actionsFilter"]["#type"] = "actions";
            $form["actionsFilter"]["filterVisitors"] = [
                "#type" => "submit",
                "#value" => $this->t("Filter"),
                "#button_type" => "primary",
                "#submit" => [[$this, "submitFormFilterVisitors"]],
            ];

            $form["actionsMerge"]["#type"] = "actions";
            $form["actionsMerge"]["mergeVisitors"] = [
                "#type" => "submit",
                "#value" => $this->t("Merge"),
                "#button_type" => "primary",
                "#submit" => [[$this, "submitFormMergeVisitors"]],
            ];

            $form["actionsPersona"]["#type"] = "actions";
            $form["actionsPersona"]["savePersona"] = [
                "#type" => "submit",
                "#value" => $this->t("Apply Persona"),
                "#button_type" => "primary",
                "#submit" => [[$this, "submitFormApplyPersona"]],
            ];
        }
    }
}
