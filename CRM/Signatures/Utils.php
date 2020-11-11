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

class CRM_Signatures_Utils {

  /**
   * Retrieves contact-specific CiviCRM settings.
   *
   * @param $contact_id
   *
   * @return \Civi\Core\SettingsBag
   * @throws \CRM_Core_Exception
   */
  public static function contactSettings($contact_id) {
    // For CiviCRM 5.7+ we can use the new Civi::contactSettings() facade.
    if (version_compare(CRM_Utils_System::version(), '5.7', '<')) {
      $settings = Civi::service('settings_manager')
        ->getBagByContact(NULL, $contact_id);
    }
    else {
      $settings = Civi::contactSettings($contact_id);
    }

    return $settings;
  }

}
