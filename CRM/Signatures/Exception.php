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

/**
 * CRM_Signatures_Signatures objects are sets of signatures for a contact.
 */
class CRM_Signatures_Exception extends Exception {

  /**
   * @var array<string, mixed>
   *   Additional parameters.
   */
  protected array $extraParams;

  /**
   * CRM_Signatures_Exception constructor.
   *
   * @param string $message
   * @param string $code
   * @param array<string, mixed> $extraParams
   * @param Throwable | NULL $previous
   */
  public function __construct(
    string $message = '',
    string $code = '',
    array $extraParams = [],
    ?Throwable $previous = NULL
  ) {
    parent::__construct($message, 0, $previous);
    $this->code = $code;
    $this->extraParams = $extraParams;
  }

  /**
   * @return array<string, mixed>
   */
  public function getExtraParams(): array {
    return $this->extraParams;
  }

}
