<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Form\AbstractStatForm;
use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * StatVisitorForm
 */
class StatVisitorForm extends AbstractStatForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_stat_visitor_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $visitor = $this->getDefaultQueryValue(Self::INPUT_GET_ID);
        $form_state->set("visitor", $visitor);

        $visitorRecord = StatDatabase::getStatVisitor($visitor);

        if (empty($visitorRecord)) {
            $form["empty"] = [
                "#type" => "markup",
                "#markup" =>
                    "<strong>There is no visitor with this ID.</strong>",
            ];
        } else {
            $form["#theme"] = $this->getTheme();

            $form["visitorName"] = [
                "#type" => "textfield",
                "#field_prefix" => $this->t("<strong>Visitor: </strong>"),
                "#default_value" => $visitorRecord["VIS_NAME"],
                "#size" => 30,
                "#maxlength" => 30,
                "#required" => false,
            ];

            $form["visitor"] = [
                "#type" => "markup",
                "#markup" => "<strong>ID: </strong>" . $visitor,
            ];

            $form["ip"] = [
                "#type" => "markup",
                "#markup" => "<strong>IP: </strong>" . $visitorRecord["IP"],
            ];

            $form["host"] = [
                "#type" => "markup",
                "#markup" => "<strong>Host: </strong>" . $visitorRecord["HOST"],
            ];

            $form["agent"] = [
                "#type" => "markup",
                "#markup" => "<strong>Agent: </strong>" . $visitorRecord["AGENT"],
            ];

            $form["geolocalisation"] = [
                "#type" => "markup",
                "#markup" =>
                    "<strong>GeoLocalisation: </strong>" .
                    $visitorRecord["CONTINENT"] . " - " .
                    $visitorRecord["COUNTRY"] . " - " .
                    $visitorRecord["REGION"] . " - " .
                    $visitorRecord["CITY"],
            ];

            $form_state->set("persona", $visitorRecord["PERSONA_CODE"]);
            $form["personaSelect"] = $this->getSelect($form_state, "persona");

            $form_state->set("browser", $visitorRecord["BROWSER_CODE"]);
            $form["browserSelect"] = $this->getSelect($form_state, "browser");

            $form["table_logins"] = [
                "#type" => "table",
                "#header" => [$this->t("Login"), $this->t("Start Date"), $this->t("End Date")],
                "#rows" => StatDatabase::getStatVisitorLoginsRows($visitor),
                "#empty" => $this->t("No data"),
            ];

            $form["table_uuids"] = [
                "#type" => "table",
                "#header" => [
                    $this->t("UUID"),
                    $this->t("Start Date"),
                    $this->t("End Date"),
                ],
                "#rows" => StatDatabase::getStatVisitorUUIDsRows($visitor),
                "#empty" => $this->t("No data"),
            ];

            $form["table_ips"] = [
                "#type" => "table",
                "#header" => [
                    $this->t("IP"),
                    $this->t("Start Date"),
                    $this->t("End Date"),
                ],
                "#rows" => StatDatabase::getStatVisitorIPsRows($visitor),
                "#empty" => $this->t("No data"),
            ];

            $form["chart_types"] = [
                "#type" => "markup",
                "#markup" => '<div id="StatVisitorTypesPie"></div>',
                "#children" => StatDatabase::getStatVisitorTypesPie($visitor),
            ];

            $form["actionsPersona"]["#type"] = "actions";
            $form["actionsPersona"]["savePersona"] = [
                "#type" => "submit",
                "#value" => $this->t("Save Persona"),
                "#button_type" => "primary",
                "#submit" => [[$this, "submitFormPersona"]],
            ];

            $form["actionsBrowser"]["#type"] = "actions";
            $form["actionsBrowser"]["saveBrowser"] = [
                "#type" => "submit",
                "#value" => $this->t("Save Browser"),
                "#button_type" => "primary",
                "#submit" => [[$this, "submitFormBrowser"]],
            ];

            $form["actionsName"]["#type"] = "actions";
            $form["actionsName"]["saveName"] = [
                "#type" => "submit",
                "#value" => $this->t("Save Name"),
                "#button_type" => "primary",
                "#submit" => [[$this, "submitFormName"]],
            ];

            $target = $this->getDefaultSelectValue($form_state, "target");
            $form["targetSelect"] = $this->getSelect($form_state, "target");

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
                    Self::DEFAULT_VALUE_INT,
                    $visit,
                    $traffic,
                    $type,
                    $warning,
                    $visitor
                ),
                "#empty" => $this->t("No data"),
            ];
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $this->validateVisitor($form_state);
        $this->validateSelectNum($form_state, "persona");
        $this->validateSelectNum($form_state, "browser");

        $this->validateSelectNum($form_state, "target");
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
        $this->initaliseSelecttvalue($form_state, "target");
        $this->initaliseSelecttvalue($form_state, "visit");
        $this->initaliseSelecttvalue($form_state, "traffic");
        $this->initaliseSelecttvalue($form_state, "type");
        $this->initaliseSelecttvalue($form_state, "warning");

        parent::submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitFormPersona(
        array              &$form,
        FormStateInterface $form_state
    )
    {
        StatDatabase::saveStatVisitorPersona(
            $form_state->get("visitor"),
            $form_state->getValue("personaSelect")
        );

        parent::submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitFormBrowser(
        array              &$form,
        FormStateInterface $form_state
    )
    {
        StatDatabase::saveStatVisitorBrowser(
            $form_state->get("visitor"),
            $form_state->getValue("browserSelect")
        );

        parent::submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitFormName(
        array              &$form,
        FormStateInterface $form_state
    )
    {
        StatDatabase::saveStatVisitorName(
            $form_state->get("visitor"),
            $form_state->getValue("visitorName")
        );

        parent::submitForm($form, $form_state);
    }
}
