<?php

namespace Drupal\alberora\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\alberora\Form\AbstractStatForm;
use Drupal\alberora\Database\StatDatabase;
use Drupal\alberora\Log\StatLog;

/**
 * StatAdminForm
 */
class StatAdminForm extends AbstractStatForm
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return "alberora_stat_admin_form";
    }

    /**
     * {}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $form["#theme"] = $this->getTheme();

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        parent::submitForm($form, $form_state);
    }
}
