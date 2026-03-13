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

declare(strict_types = 1);

use CRM_Signatures_ExtensionUtil as E;

/**
 * CRM_Signatures_Signatures objects are sets of signatures for a contact.
 */
class CRM_Signatures_Signatures {

  /**
   * @var \CRM_Signatures_Signatures[]
   *   Caches the list of sets of signatures.
   */
  protected static ?array $_signatures = NULL;

  /**
   * @var string
   *   The contact's ID the signatures belong to.
   */
  protected string $contact_id;

  /**
   * @var array<string, mixed>|NULL
   *   The signatures data.
   */
  protected ?array $data = NULL;

  /**
   * CRM_Signatures_Signatures constructor.
   *
   * @param string $contact_id
   *   The contact's ID.
   * @param array $data
   *   The signatures data.
   * @phpstan-ignore missingType.iterableValue
   */
  public function __construct($contact_id, $data = []) {
    $this->contact_id = (string) $contact_id;
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
   * @phpstan-ignore missingType.iterableValue
   */
  public function getData(): array {
    return (array) $this->data;
  }

  /**
   * Retrieves the contact ID.
   *
   * @return string
   */
  public function getContactID(): string {
    return $this->contact_id;
  }

  /**
   * Sets the contact ID.
   *
   * @param string $contact_id
   */
  public function setContactID($contact_id): void {
    $this->contact_id = $contact_id;
  }

  /**
   * Retrieves a signature.
   *
   * @param string $signature_name
   *
   * @return mixed | NULL
   */
  public function getSignature($signature_name): mixed {
    if (isset($this->data[$signature_name])) {
      return $this->data[$signature_name];
    }

    return NULL;
  }

  /**
   * Sets a signature.
   *
   * @param string $signature_name
   * @param mixed $signature_body
   *
   * @throws \CRM_Signatures_Exception
   *   When the signature name is not known.
   */
  public function setSignature($signature_name, $signature_body): void {
    if (!array_key_exists($signature_name, self::allowedSignatures())) {
      throw new CRM_Signatures_Exception(
        E::ts('Unknown signature name %1', [1 => $signature_name]),
        'signatures_unknown_signature',
        [
          'signature_name' => $signature_name,
        ]
      );
    }
    // TODO: Check if value is acceptable.
    $this->data[$signature_name] = $signature_body;
  }

  /**
   * Verifies whether the signatures are valid.
   *
   * @throws \CRM_Signatures_Exception
   *   When the signatures could not be successfully validated.
   */
  public function verifySignatures(): void {
    // Serialize and check for allowed database column length. The MySQL data
    // type for settings data is TEXT, which allows 2^16 bytes for the actual
    // value.
    $blob = serialize((object) $this->getData());
    if (strlen($blob) > pow(2, 16)) {
      throw new CRM_Signatures_Exception(
        E::ts('Signatures data is too long.'),
        'signatures_too_long'
      );
    }
  }

  /**
   * Persists the signatures within the CiviCRM settings.
   *
   * @throws \CRM_Signatures_Exception
   */
  public function saveSignatures(): void {
    self::$_signatures[$this->getContactID()] = $this;
    $this->verifySignatures();
    $signatures_data = [];
    foreach ($this->getData() as $signature_name => $signature) {
      $signatures_data[$signature_name] = base64_encode($signature);
    }

    CRM_Signatures_Utils::contactSettings($this->getContactID())
      ->set('signatures_signatures', $signatures_data);
  }

  /**
   * Deletes the signatures from the CiviCRM settings.
   */
  public function deleteSignatures(): void {
    if (isset(self::$_signatures[$this->getContactID()])) {
      unset(self::$_signatures[$this->getContactID()]);
      CRM_Signatures_Utils::contactSettings($this->getContactID())
        ->set('signatures_signatures', NULL);
    }
  }

  /**
   * Returns an array of allowed signature names.
   *
   * @return array
   * @phpstan-ignore missingType.iterableValue
   */
  public static function allowedSignatures(): array {
    return [
      'signature_letter_html' => E::ts('Letter signature (HTML)'),
      'signature_email_html' => E::ts('E-mail signature (HTML)'),
      'signature_email_plain' => E::ts('E-mail signature (plain text)'),
      'signature_mass_mailing_html' => E::ts('Mass mailing signature (HTML)'),
      'signature_mass_mailing_plain' => E::ts('Mass mailing signature (plain text)'),
      'signature_additional_html' => E::ts('Additional signature (HTML)'),
      'signature_additional_plain' => E::ts('Additional signature (plain text)'),
    ];
  }

  /**
   * Retrieves the signatures for the given contact ID.
   *
   * @param string $contact_id
   *
   * @return CRM_Signatures_Signatures | NULL
   */
  public static function getSignatures($contact_id): ?CRM_Signatures_Signatures {
    if (!isset(self::$_signatures[$contact_id])) {
      $signatures_data = [];
      try {
        $signatures_raw = CRM_Signatures_Utils::contactSettings($contact_id)
          ->get('signatures_signatures');
        if (is_iterable($signatures_raw)) {
          foreach ($signatures_raw as $signature_name => $signature_raw) {
            $signatures_data[$signature_name] = base64_decode($signature_raw, TRUE);
          }
        }
      }
      catch (Exception $exception) {
        // There is no contact with that ID.
        // @ignoreException
        Civi::log()
          ->debug(
            "Signature Extension: Contact ID '{$contact_id}' caused exception: " . $exception->getMessage()
          );
      }
      self::$_signatures[$contact_id] = new self($contact_id, $signatures_data);
    }

    return self::$_signatures[$contact_id];
  }

}
