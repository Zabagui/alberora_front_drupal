<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Form\AbstractStatForm;
use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * StatLoginForm
 */
class StatLoginForm extends AbstractStatForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_stat_login_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $login = $this->getDefaultQueryValue(Self::INPUT_GET_ID);
        $name = "";
        $uuid = "";
        $ip = "";
        $fieldName = "Login";
        $FieldValue = $login;

        $form_state->set("login", $login);
        $record = StatDatabase::getStatLogin($login);

        $this->DisplayDetailedForm($form, $form_state, $record, $fieldName, $FieldValue, $login, $name, $uuid, $ip);

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
        parent::submitForm($form, $form_state);
    }
}
