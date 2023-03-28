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
 * Collection of upgrade steps.
 */
class CRM_Signatures_Upgrader extends CRM_Extension_Upgrader_Base {

  /**
   * Split the signatures settings into contact-specific settings.
   *
   * @return bool
   *   Whether the upgrade task executed successfully.
   */
  public function upgrade_4000() {
    // Signatures data may be corrupted due to serialization errors when
    // containing special characters. We try to recover them.
    // Using CRM_Core_BAO_Setting::getItem() is thus not sufficient, as it just
    // tries to unserialize() the data.

    // Retrieve the serialization string from the database directly.
    $value = CRM_Core_DAO::singleValueQuery(
      'SELECT `value` from `civicrm_setting` WHERE `name` = "signatures_signatures";'
    );

    // Try to unserialize original string.
    $all_signatures = unserialize($value);

    // In case of failure try to repair it.
    if($all_signatures === FALSE){
      $repairedSerialization = static::fix_serialized($value);
      $all_signatures = unserialize($repairedSerialization);
    }

    if (!empty($all_signatures)) {
      $all_signatures = (array) $all_signatures;
      foreach ($all_signatures as $contact_id => $signatures) {
        try {
          $signatures_object = new CRM_Signatures_Signatures($contact_id, $signatures);
          $signatures_object->verifySignatures();
          $signatures_data = array();
          foreach ($signatures_object->getData() as $signature_name => $signature) {
            $signatures_data[$signature_name] = base64_encode($signature);
          }
          CRM_Signatures_Utils::contactSettings($contact_id)
            ->set('signatures_signatures', $signatures_data);
        }
        catch (Exception $exception) {
          CRM_Core_Session::setStatus(
            E::ts(
              'Could not process signatures for contact with ID %1. You may have to re-create them manually.',
              array(1 => $contact_id)
            ),
            E::ts('Signatures database upgrade'),
            'error'
          );
          continue;
        }
      }
    }

    // Remove the old settings entry (Set it to NULL).
    Civi::settings()
      ->revert('signatures_signatures');

    return TRUE;
  }

  /**
   * Convert serialized settings from objects to arrays.
   *
   * @link https://civicrm.org/advisory/civi-sa-2019-21-poi-saved-search-and-report-instance-apis
   */
  public function upgrade_5011() {
    // Do not use CRM_Core_BAO::getItem() or Civi::settings()->get().
    // Extract and unserialize directly from the database.
    $signatures_query = CRM_Core_DAO::executeQuery("
        SELECT `value`, `contact_id`
          FROM `civicrm_setting`
        WHERE `name` = 'signatures_signatures';");
    while ($signatures_query->fetch()) {
      $signatures_record = unserialize($signatures_query->value);
      CRM_Signatures_Utils::contactSettings($signatures_query->contact_id)
        ->set('signatures_signatures', (array) $signatures_record);
    }

    return TRUE;
  }

  /**
   * Utility function for fixing wrong byte lengths in serialization strings.
   *
   * @see https://stackoverflow.com/a/34224433.
   *
   * @param $matches
   *
   * @return string
   */
  public static function fix_str_length($matches) {
    $string = $matches[2];
    $right_length = strlen($string); // yes, strlen even for UTF-8 characters, PHP wants the mem size, not the char count
    return 's:' . $right_length . ':"' . $string . '";';
  }

  /**
   * Utility function for repairing corrupted serialization strings.
   *
   * @see https://stackoverflow.com/a/34224433.
   *
   * @param $string
   *
   * @return null|string|string[]
   */
  public static function fix_serialized($string) {
    // securities
    if (!preg_match('/^[aOs]:/', $string)) {
      return $string;
    }
    if (@unserialize($string) !== FALSE) {
      return $string;
    }
    $string = preg_replace("%\n%", "", $string);
    // doublequote exploding
    $data = preg_replace('%";%', "µµµ", $string);
    $tab = explode("µµµ", $data);
    $new_data = '';
    foreach ($tab as $line) {
      $new_data .= preg_replace_callback('%\bs:(\d+):"(.*)%', array(
        static::class,
        'fix_str_length',
      ), $line);
    }
    return $new_data;
  }

}
