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
class CRM_Signatures_Upgrader extends CRM_Signatures_Upgrader_Base {

  /**
   * Split the signatures settings into contact-specific settings.
   *
   * @return bool
   *   Whether the upgrade task executed successfully.
   */
  public function upgrade_4200() {
    $all_signatures = CRM_Core_BAO_Setting::getItem(
      'de.systopia.signatures',
      'signatures_signatures'
    );
    foreach ($all_signatures as $contact_id => $signatures) {
      CRM_Core_BAO_Setting::setItem(
        (object) $signatures,
        'de.systopia.signatures',
        'signatures_signatures',
        NULL,
        $contact_id
      );
    }

    // Remove the old settings entry (Set it to NULL).
    CRM_Core_BAO_Setting::setItem(
      NULL,
      'de.systopia.signatures',
      'signatures_signatures'
    );

    return TRUE;
  }

}
