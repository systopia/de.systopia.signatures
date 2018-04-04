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
 * CRM_Signatures_Signatures objects are sets of signatures for a contact.
 */
class CRM_Signatures_Signatures {

  /**
   * @var \CRM_Signatures_Signatures[] $_signatures
   *   Caches the list of sets of signatures.
   */
  protected static $_signatures = NULL;

  /**
   * @var string $contact_id
   *   The contact's ID the signatures belong to.
   */
  protected $contact_id = NULL;

  /**
   * @var array $data
   *   The signatures data.
   */
  protected $data = NULL;

  /**
   * CRM_Signatures_Signatures constructor.
   *
   * @param string $contact_id
   *   The contact's ID.
   * @param array $data
   *   The signatures data.
   */
  public function __construct($contact_id, $data = array()) {
    $this->contact_id = $contact_id;
    $allowed_signatures = array_keys(self::allowedSignatures());
    $this->data = $data + array_combine(
        $allowed_signatures,
        array_fill(0, count($allowed_signatures), '')
      );
  }

  /**
   * Retrieves all signatures for the current set of signatures.
   *
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Retrieves the contact ID.
   *
   * @return string
   */
  public function getContactID() {
    return $this->contact_id;
  }

  /**
   * Sets the contact ID.
   *
   * @param $contact_id
   */
  public function setContactID($contact_id) {
    $this->contact_id = $contact_id;
  }

  /**
   * Retrieves a signature.
   *
   * @param string $signature_name
   *
   * @return mixed | NULL
   */
  public function getSignature($signature_name) {
    if (isset($this->data[$signature_name])) {
      return $this->data[$signature_name];
    }
    else {
      return NULL;
    }
  }

  /**
   * Sets a signature.
   *
   * @param string $signature_name
   * @param mixed $signature_body
   *
   * @throws \Exception
   *   When the signature name is not known.
   */
  public function setSignature($signature_name, $signature_body) {
    if (!in_array($signature_name, array_keys(self::allowedSignatures()))) {
      throw new Exception("Unknown signature name {$signature_name}.");
    }
    // TODO: Check if value is acceptable.
    $this->data[$signature_name] = $signature_body;
  }

  /**
   * Verifies whether the signatures are valid.
   *
   * @throws Exception
   *   When the signatures could not be successfully validated.
   */
  public function verifySignatures() {
    // TODO: Anything to verify?
  }

  /**
   * Persists the signatures within the CiviCRM settings.
   */
  public function saveSignatures() {
    self::$_signatures[$this->getContactID()] = $this;
    $this->verifySignatures();
    self::storeSignatures();
  }

  /**
   * Deletes the signatures from the CiviCRM settings.
   */
  public function deleteSignatures() {
    unset(self::$_signatures[$this->getContactID()]);
    self::storeSignatures();
  }

  /**
   * Returns an array of allowed signature names.
   *
   * @return array
   */
  public static function allowedSignatures() {
    return array(
      'signature_letter_html' => E::ts('Letter signature (HTML)', array('domain' => 'de.systopia.signatures')),
      'signature_email_html' => E::ts('E-mail signature (HTML)', array('domain' => 'de.systopia.signatures')),
      'signature_email_plain' => E::ts('E-mail signature (plain text)', array('domain' => 'de.systopia.signatures')),
      'signature_mass_mailing_html' => E::ts('Mass mailing signature (HTML)', array('domain' => 'de.systopia.signatures')),
      'signature_mass_mailing_plain' => E::ts('Mass mailing signature (plain text)', array('domain' => 'de.systopia.signatures')),
    );
  }

  /**
   * Retrieves the signatures for the given contact ID.
   *
   * @param $contact_id
   *
   * @return CRM_Signatures_Signatures | NULL
   */
  public static function getSignatures($contact_id) {
    $signatures = self::getAllSignatures();
    if (isset($signatures[$contact_id])) {
      return $signatures[$contact_id];
    }
    else {
      return NULL;
    }
  }

  /**
   * Retrieves the list of all sets of signatures persisted within the current
   * CiviCRM settings.
   *
   * @return CRM_Signatures_Signatures[]
   */
  public static function getAllSignatures() {
    if (self::$_signatures === NULL) {
      self::$_signatures = array();
      if ($all_signatures_data = CRM_Core_BAO_Setting::getItem('de.systopia.signatures', 'signatures_signatures')) {
        foreach ($all_signatures_data as $contact_id => $signatures_data) {
          self::$_signatures[$contact_id] = new CRM_Signatures_Signatures($contact_id, $signatures_data);
        }
      }
    }

    return self::$_signatures;
  }

  /**
   * Persists the list of all loaded sets of signatures into the CiviCRM
   * settings.
   */
  public static function storeSignatures() {
    $signatures_data = array();
    foreach (self::$_signatures as $contact_id => $signatures) {
      $signatures_data[$contact_id] = $signatures->data;
    }
    CRM_Core_BAO_Setting::setItem((object) $signatures_data, 'de.systopia.signatures', 'signatures_signatures');
  }
}
