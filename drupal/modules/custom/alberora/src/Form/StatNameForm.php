<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Form\AbstractStatForm;
use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * StatNameForm
 */
class StatNameForm extends AbstractStatForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_stat_name_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $login = "";
        $name = $this->getDefaultQueryValue(Self::INPUT_GET_ID);
        $uuid = "";
        $ip = "";
        $fieldName = "Name";
        $FieldValue = $name;

        $form_state->set("name", $name);
        $record = StatDatabase::getStatName($name);

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
