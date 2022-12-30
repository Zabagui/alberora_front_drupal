<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Form\AbstractStatForm;
use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * StatVisitorsForm
 */
class StatVisitorsForm extends AbstractStatForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_stat_visitors_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form["#theme"] = $this->getTheme();

        $persona = $this->getDefaultSelectValue($form_state, "persona");
        $form["personaSelect"] = $this->getSelect($form_state, "persona");

        $human = $this->getDefaultSelectValue($form_state, "human");
        $form["humanSelect"] = $this->getSelect($form_state, "human");

        $browser = $this->getDefaultSelectValue($form_state, "browser");
        $form["browserSelect"] = $this->getSelect($form_state, "browser");

        $form["table_visitors"] = [
            "#type" => "table",
            "#header" => [
                $this->t("Visitor (Parent)"),
                $this->t("IPs"),
                $this->t("Logins"),
                $this->t("UUIDs"),
                $this->t("Agent"),
                $this->t("Characteristics"),
                $this->t("Geolocalisation"),
                $this->t("Updated"),
            ],
            "#rows" => StatDatabase::getStatVisitorsRows($persona, $human, $browser),
            "#empty" => $this->t("No data"),
        ];

        $form["actions"]["#type"] = "actions";
        $form["actions"]["submit"] = [
            "#type" => "submit",
            "#value" => $this->t("Filter"),
            "#button_type" => "primary",
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $this->validateSelectNum($form_state, "persona");
        $this->validateSelectNum($form_state, "human");
        $this->validateSelectNum($form_state, "browser");
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->initaliseSelecttvalue($form_state, "persona");
        $this->initaliseSelecttvalue($form_state, "human");
        $this->initaliseSelecttvalue($form_state, "browser");

        parent::submitForm($form, $form_state);
    }
}
