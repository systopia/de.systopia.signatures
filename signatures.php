<?php

require_once 'signatures.civix.php';
use CRM_Signatures_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function signatures_civicrm_config(&$config) {
  _signatures_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function signatures_civicrm_install() {
  _signatures_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function signatures_civicrm_enable() {
  _signatures_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function signatures_civicrm_navigationMenu(&$menu) {
  _signatures_civix_insert_navigation_menu($menu, 'Contacts', array(
    'label' => E::ts('Signatures', array('domain' => 'de.systopia.signatures')),
    'name' => 'signatures',
    'url' => 'civicrm/contact/signatures',
    'permission' => 'access CiviCRM',
    'operator' => 'OR',
    'separator' => 2,
  ));
  _signatures_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_tokens().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokens
 */
function signatures_civicrm_tokens(&$tokens) {
  foreach (CRM_Signatures_Signatures::allowedSignatures() as $signature_type => $signature_label) {
    $tokens['signatures']['signatures.' . $signature_type] = $signature_label;
  }
}

/**
 * Implements hook_civicrm_tokenValues().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokenValues
 */
function signatures_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  if (array_key_exists('signatures', $tokens)) {
    // Retrieve the mass mailing creator, or the logged-in contact's ID.
    if (!empty($job) && !empty($context)) {
      try {
        $mailing_job_result = civicrm_api3('MailingJob', 'getsingle', array(
          'id' => $job,
          'return' => array("mailing_id"),
        ));
        if (empty($mailing_job_result['mailing_id'])) {
          throw new Exception('Error retrieving MailingJob with ID ' . $job . '. Error returned: ' . $mailing_job_result['error_message']);
        }

        $mailing_result = civicrm_api3('Mailing', 'getsingle', array(
          'id' => $mailing_job_result['mailing_id'],
          'return' => array("created_id"),
        ));
        if (empty($mailing_result['created_id'])) {
          throw new Exception('Error retrieving Mailing with ID ' . $mailing_result['mailing_id'] . '. Error returned: ' . $mailing_job_result['error_message']);
        }

        $contact_id = $mailing_result['created_id'];

      }
      catch (Exception $exception) {
        CRM_Core_Error::debug_log_message('de.systopia.signatures:tokenValues():Could not retrieve contact ID from MailingJob. Trying logged-in contact. Exception caught: ' . $exception->getMessage());
      }
    }

    if (empty($contact_id) && !$contact_id = CRM_Core_Session::singleton()->getLoggedInContactID()) {
      CRM_Core_Error::debug_log_message('de.systopia.signatures:tokenValues():Could not retrieve contact ID for signature.');
    }

    // Fetch signatures and fill token values.
    if ($signatures = CRM_Signatures_Signatures::getSignatures($contact_id)) {
      foreach ($cids as $cid) {
        foreach ($signatures->getData() as $signature_name => $signature_body) {
          $values[$cid]['signatures.' . $signature_name] = $signature_body;
        }
      }
    }
  }
}
