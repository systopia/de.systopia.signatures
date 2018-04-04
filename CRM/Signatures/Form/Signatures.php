<?php
/*------------------------------------------------------------+
| SYSTOPIA Signatures                                         |
| Copyright (C) 2017 SYSTOPIA                                 |
| Author: B. Endres (endres@systopia.de)                      |
|         J. Schuppe (schuppe@systopia.de)                    |
+-------------------------------------------------------------+
| This program is released as free software under the         |
| Affero GPL license. You can redistribute it and/or          |
| modify it under the terms of this license which you         |
| can read by viewing the included agpl.txt or online         |
| at www.gnu.org/licenses/agpl.html. Removal of this          |
| copyright header is strictly prohibited without             |
| written permission from the original author(s).             |
+-------------------------------------------------------------*/

use CRM_Signatures_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Signatures_Form_Signatures extends CRM_Core_Form {

  /**
   * @var \CRM_Signatures_Signatures $signatures
   *   The set of signatures for the contact.
   */
  protected $signatures;

  /**
   * Builds the form.
   */
  public function buildQuickForm() {
    $contact_id = $this->setContactID();

    if (!$this->signatures = CRM_Signatures_Signatures::getSignatures($contact_id)) {
      $this->signatures = new CRM_Signatures_Signatures($contact_id);
    }

    // Add form elements.
    $this->add(
      'wysiwyg',
      'signature_letter_html',
      E::ts('Letter signature (HTML)', array('domain' => 'de.systopia.signatures'))
    );
    $this->add(
      'wysiwyg',
      'signature_email_html',
      E::ts('E-mail signature (HTML)', array('domain' => 'de.systopia.signatures'))
    );
    $this->add(
      'textarea',
      'signature_email_plain',
      E::ts('E-mail signature (plain text)', array('domain' => 'de.systopia.signatures'))
    );
    $this->add(
      'wysiwyg',
      'signature_mass_mailing_html',
      E::ts('Mass mailing signature (HTML)', array('domain' => 'de.systopia.signatures'))
    );
    $this->add(
      'textarea',
      'signature_mass_mailing_plain',
      E::ts('Mass mailing signature (plain text)', array('domain' => 'de.systopia.signatures'))
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // Export form elements.
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('contactID', $contact_id);
    $this->assign('header', E::ts('You are editing signatures for the contact with the ID <em>%1</em>', array(
      'domain' => 'de.systopia.signatures',
      1 => $contact_id,
    )));

    parent::buildQuickForm();
  }

  /**
   * Set the default values (signatures' current data) in the form.
   */
  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();

    $defaults['contact_id'] = $this->signatures->getContactID();
    foreach ($this->signatures->getData() as $signature_name => $signature_body) {
      $defaults[$signature_name] = $signature_body;
    }
    return $defaults;
  }

  /**
   * Processes the submitted form.
   */
  public function postProcess() {
    $values = $this->exportValues();
    $contact_id = $this->getContactID();

    $this->signatures->setContactID($contact_id);
    foreach ($this->signatures->getData() as $signature_name => $signature_body) {
      if (isset($values[$signature_name])) {
        $this->signatures->setSignature($signature_name, $values[$signature_name]);
      }
    }
    $this->signatures->saveSignatures();

    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
