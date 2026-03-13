<?php

declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'signatures.civix.php';
// phpcs:enable

use CRM_Signatures_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function signatures_civicrm_config(\CRM_Core_Config &$config): void {
  _signatures_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function signatures_civicrm_install(): void {
  _signatures_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function signatures_civicrm_enable(): void {
  _signatures_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @phpstan-param array<string, array<string, mixed>> $menu
 * @param-out array<string, array<string, mixed>> $menu
 */
function signatures_civicrm_navigationMenu(array &$menu): void {
  // @phpstan-ignore paramOut.type
  _signatures_civix_insert_navigation_menu($menu, 'Contacts', [
    'label' => E::ts('Signatures'),
    'name' => 'signatures',
    'url' => 'civicrm/contact/signatures',
    'permission' => 'access CiviCRM',
    'operator' => 'OR',
    'separator' => 2,
  ]);
  _signatures_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_tokens().
 *
 * @deprecated Deprecated - use the token Processor
 */
function signatures_civicrm_tokens(array &$tokens): void {
  foreach (CRM_Signatures_Signatures::allowedSignatures() as $signature_type => $signature_label) {
    $tokens['signatures']['signatures.' . $signature_type] = $signature_label;
  }
}

/**
 * Implements hook_civicrm_tokenValues().
 *
 * @deprecated This hook is deprecated - you should use the token processor methodology to offer custom tokens
 */
// phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Files.LineLength.TooLong
function signatures_civicrm_tokenValues(array &$values, $cids, $job = NULL, array $tokens = [], $context = NULL): void {
  if (array_key_exists('signatures', $tokens)) {
    // Retrieve the mass mailing creator, or the logged-in contact's ID.
    if ((NULL !== $job && 0 !== $job) && (NULL !== $context && '' !== $context)) {
      try {
        /** @var array<string, int|string> $mailing_job_result */
        $mailing_job_result = civicrm_api3('MailingJob', 'getsingle', [
          'id' => $job,
          'return' => ['mailing_id'],
        ]);
        if (!isset($mailing_job_result['mailing_id'])
          || (int) $mailing_job_result['mailing_id'] <= 0
          || $mailing_job_result['mailing_id'] === ''
        ) {
          /** @var string $errorMessage */
          $errorMessage = $mailing_job_result['error_message'];
          // phpcs:ignore Generic.Files.LineLength.TooLong
          throw new RuntimeException('Error retrieving MailingJob with ID ' . $job . '. Error returned: ' . $errorMessage);
        }
        /** @var array<string, string|int> $mailing_result */
        $mailing_result = civicrm_api3('Mailing', 'getsingle', [
          'id' => $mailing_job_result['mailing_id'],
          'return' => ['created_id'],
        ]);
        if (!isset($mailing_result['created_id'])
          || $mailing_result['created_id'] <= 0
          || $mailing_result['created_id'] === ''
        ) {
          // phpcs:ignore Generic.Files.LineLength.TooLong
          throw new RuntimeException('Error retrieving Mailing with ID ' . $mailing_result['mailing_id'] . '. Error returned: ' . $mailing_job_result['error_message']);
        }

        $contact_id = $mailing_result['created_id'];

      }
      catch (Exception $exception) {
        // @ignoreException
        // phpcs:ignore Generic.Files.LineLength.TooLong
        Civi::log()->error('de.systopia.signatures:tokenValues():Could not retrieve contact ID from MailingJob. Trying logged-in contact. Exception caught: ' . $exception->getMessage());
      }
    }

    $contact_id = isset($contact_id) ? (string) $contact_id : '';
    if ($contact_id === '') {
      $contact_id = (string) CRM_Core_Session::singleton()::getLoggedInContactID();
    }
    if ($contact_id === '') {
      Civi::log()->error('de.systopia.signatures:tokenValues():Could not retrieve contact ID for signature.');
    }

    // Fetch signatures and fill token values.
    $signatures = CRM_Signatures_Signatures::getSignatures($contact_id);
    if (NULL !== $signatures) {
      foreach ($cids as $cid) {
        foreach ($signatures->getData() as $signature_name => $signature_body) {
          $values[$cid]['signatures.' . $signature_name] = $signature_body;
        }
      }
    }
  }
}
