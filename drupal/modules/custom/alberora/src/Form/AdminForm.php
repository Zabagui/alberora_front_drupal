<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

use Drupal\alberora\Database\StatDatabase;

/**
 * Settings Alberora module.
 */
class AdminForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_admin_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config("alberora.settings");

        $form["Stat_configuration"] = [
            "#type" => "fieldset",
            "#title" => $this->t("Stat Configuration"),
        ];

        $form["Stat_configuration"]["stat_visitor_page"] = [
            "#type" => "textfield",
            "#title" => $this->t("Stat visitor page"),
            "#default_value" => $config->get("statVisitorPage"),
            "#required" => false,
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return ["alberora.settings"];
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config("alberora.settings")
            ->set("statVisitorPage", $form_state->getValue("stat_visitor_page"))
            ->save();

        parent::submitForm($form, $form_state);
    }
}
