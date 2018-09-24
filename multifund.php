<?php

require_once 'multifund.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function multifund_civicrm_config(&$config) {
  _multifund_civix_civicrm_config($config);
}

function multifund_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Grant_Form_Grant' && !($form->_action & CRM_Core_Action::DELETE)) {
    $result = civicrm_api3('FinancialAccount', 'get', [])['values'];
    $totalCount = count($result);
    $financialOptions = ['' => '- select -'] + CRM_Utils_Array::collect('name', $result);
    $submittedValues = [];
    for ($i = 0; $i < $totalCount; $i++) {
      if (!empty($_POST['multifund_amount']) && !empty($_POST['multifund_amount'][$i])) {
        $submittedValues[] = $i;
      }
      $form->add('select', "financial_account[$i]", ts('Source Fund'), $financialOptions);
      $form->add('text', "multifund_amount[$i]", ts('Amount'));
    }
    $form->assign('totalCount', $totalCount);
    $form->assign('itemSubmitted', json_encode($submittedValues));
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/MultifundItems.tpl",
    ));
  }
}

function multifund_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Grant_Form_Grant' && !($form->_action & CRM_Core_Action::DELETE)) {
    $values = $form->exportValues();
    $totalCount = civicrm_api3('FinancialAccount', 'getcount', []);
    $multifundEntries = [];
    for ($i = 0; $i < $totalCount; $i++) {
      if (!empty($values['financial_account'][$i])) {
        $multifundEntries[$i] = [
          'from_financial_account_id' => $values['financial_account'][$i],
          'total_amount' => $values['multifund_amount'][$i],
        ];
      }
    }
    if (!empty($multifundEntries)) {
      CRM_Core_BAO_Cache::setItem($multifundEntries, 'multifund entries', __FUNCTION__);
    }
  }
}

function multifund_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Grant_Form_Grant') {
    $multiEntries = [];
    $amount = CRM_Utils_Array::value('amount_total', $fields);
    $totalAmount = 0.00;
    $found = FALSE;
    foreach($fields['multifund_amount'] as $i => $value) {
      if (!empty($value)) {
        $found = TRUE;
        $totalAmount += $value;
      }
      if ($totalAmount > $amount) {
        $errors["multifund_amount[$i]"] = ts('Sum of all source fund amounts exceeds the grant amount requested. Please adjust the source fund amount');
      }
    }
    if ($found && $totalAmount < $amount) {
      $errors["multifund_amount[0]"] = ts('Sum of all source fund amounts is less than the grant amount requested. Please adjust the source fund amount');
    }
  }
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function multifund_civicrm_xmlMenu(&$files) {
  _multifund_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function multifund_civicrm_install() {
  _multifund_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function multifund_civicrm_postInstall() {
  _multifund_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function multifund_civicrm_uninstall() {
  _multifund_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function multifund_civicrm_enable() {
  _multifund_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function multifund_civicrm_disable() {
  _multifund_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function multifund_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _multifund_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function multifund_civicrm_managed(&$entities) {
  _multifund_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function multifund_civicrm_caseTypes(&$caseTypes) {
  _multifund_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function multifund_civicrm_angularModules(&$angularModules) {
  _multifund_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function multifund_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _multifund_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function multifund_civicrm_entityTypes(&$entityTypes) {
  _multifund_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function multifund_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function multifund_civicrm_navigationMenu(&$menu) {
  _multifund_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _multifund_civix_navigationMenu($menu);
} // */
