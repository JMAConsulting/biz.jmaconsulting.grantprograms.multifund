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
    $financialEntries = $_POST;
    $defaults = [];
    if ($form->_action & CRM_Core_Action::UPDATE) {
      $financialEntries = _getFinancialEntries($form->getVar('_id'));
    }
    $result = civicrm_api3('FinancialAccount', 'get', ['financial_account_type_id' => "Expenses"])['values'];
    $financialOptions = ['' => '- select source fund -'] + CRM_Utils_Array::collect('name', $result);
    $submittedValues = [];
    for ($i = 0; $i < 2; $i++) {
      if ((!empty($financialEntries['multifund_amount']) && !empty($financialEntries['multifund_amount'][$i])) || (!empty($financialEntries['financial_account']) && !empty($financialEntries['financial_account'][$i]))) {
        $submittedValues[] = $i;
        if ($form->_action & CRM_Core_Action::UPDATE) {
          $defaults["multifund_amount[$i]"] = $financialEntries['multifund_amount'][$i];
          $defaults["financial_account[$i]"] = $financialEntries['financial_account'][$i];
        }
      }
      $form->add('select', "financial_account[$i]", ts('Source Fund'), $financialOptions);
      $form->add('text', "multifund_amount[$i]", ts('Amount'), ['placeholder' => 'amount']);
    }
    if (!empty($defaults)) {
      $form->setDefaults($defaults);
    }
    $form->assign('totalCount', 2);
    $form->assign('itemSubmitted', json_encode($submittedValues));
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => "CRM/MultifundItems.tpl",
    ));
  }
}

function _getFinancialEntries($grantID) {
  $sql = "SELECT fi.total_amount as amount, fi.from_financial_account_id, fi.id
    FROM civicrm_financial_trxn fi
     INNER JOIN civicrm_entity_financial_trxn eft ON eft.financial_trxn_id = fi.id AND eft.entity_table = 'civicrm_grant' AND eft.entity_id = $grantID
   ";
   $entries = [
     'multifund_amount' => [],
     'financial_account' => [],
     'id' => [],
   ];
   foreach (CRM_Core_DAO::executeQuery($sql)->fetchAll() as $id => $entry) {
     $entries['multifund_amount'][$id] = $entry['amount'];
     $entries['financial_account'][$id] = $entry['from_financial_account_id'];
     $entries['id'][$id] = $entry['id'];
   }

   return $entries;
}


function multifund_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Grant_Form_Grant') {
    $amount = CRM_Utils_Array::value('amount_total', $fields);
    $totalAmount = 0.00;
    $found = FALSE;
    for ($i = 0; $i < 2; $i++) {
      if (!empty($fields['multifund_amount'][$i])) {
        $found = TRUE;
        $totalAmount += $fields['multifund_amount'][$i];
      }
    }
    if ($found && $totalAmount != $amount) {
      $errors["multifund_amount[0]"] = ts('Sum of all source fund amounts is less than the grant amount requested. Please adjust the source fund amount');
    }
    if ($fields['financial_account'][0] != '' && $fields['financial_account'][0] == $fields['financial_account'][1]) {
      $errors["multifund_amount[0]"] = ts('Source funds must be unique');
    }
  }
}

function multifund_civicrm_pageRun( &$page ) {
  if ($page->getVar('_name') == "CRM_Grant_Page_Tab") {
    $grantID = $page->getVar('_id');
    $multiFundEntries = _getMultifundEntriesByGrant($grantID);
    if ($multiFundEntries) {
      $page->assign('multiFundEntries', $multiFundEntries);
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => "CRM/MultifundView.tpl",
      ));
    }
  }
}

function _getMultifundEntriesByGrant($grantID, $mode = 'view') {
  if (!$grantID) {
    return;
  }
  $trxns = civicrm_api3('EntityFinancialTrxn', 'get', [
    'entity_table' => 'civicrm_grant',
    'entity_id' => $grantID,
  ])['values'];
  $multifundEntries = [];
  $financialAccounts = CRM_Utils_Array::collect('name', civicrm_api3('FinancialAccount', 'get', [])['values']);
  foreach ($trxns as $id => $value) {
    $financialAccountID = civicrm_api3('FinancialTrxn', 'getvalue', ['id' => $value['financial_trxn_id'], 'return' => 'from_financial_account_id']);
    $key = $mode == 'view' ? $financialAccounts[$financialAccountID] : $financialAccountID;
    $multifundEntries[$key] = $value['amount'];
  }

  return $multifundEntries;
}

function multifund_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Grant_Form_Grant' && ($form->getVar('_action') & CRM_Core_Action::UPDATE)) {
    $financialEntries = _getFinancialEntries($form->getVar('_id'));
    $submittedValues = $form->exportValues();
    for ($i = 0; $i < 2; $i++) {
      if (!empty($financialEntries['multifund_amount'][$i]) && !empty($submittedValues['multifund_amount'][$i])) {
        $params = [
          'id' => $financialEntries['id'][$i],
          'total_amount' => $submittedValues['multifund_amount'][$i],
          'net_amount' => $submittedValues['multifund_amount'][$i],
          'from_financial_account_id' => $submittedValues['financial_account'][$i],
          'to_financial_account_id' => CRM_Contribute_PseudoConstant::getRelationalFinancialAccount($submittedValues['financial_type_id'], 'Asset Account is') ?: CRM_Grant_BAO_GrantProgram::getAssetFinancialAccountID(),
        ];
        civicrm_api3('FinancialTrxn', 'create', $params);
        if ($eftID = civicrm_api3('EntityFinancialTrxn', 'getvalue', ['financial_trxn_id' => $financialEntries['id'][$id], 'entity_table' => 'civicrm_grant', 'return' => 'id'])) {
          civicrm_api3('EntityFinancialTrxn', 'create', [
            'id' => $eftID,
            'amount' => $submittedValues['multifund_amount'][$i],
          ]);
        }
      }
    }
    if (!empty($submittedValues['financial_type_id'])) {
      $financialAccountID = CRM_Contribute_PseudoConstant::getRelationalFinancialAccount($submittedValues['financial_type_id'], 'Accounts Receivable Account is');
      if ($fiID = civicrm_api3('FinancialItem', 'getvalue', ['entity_id' => $form->getVar('_id'), 'entity_table' => 'civicrm_grant', 'return' => 'id'])) {
        civicrm_api3('FinancialItem', 'create', [
          'id' => $fiID,
          'amount' => $submittedValues['amount_total'],
          'financial_account_id' => $financialAccountID,
        ]);
        if ($eftID = civicrm_api3('EntityFinancialTrxn', 'getvalue', ['entity_id' => $fiID, 'entity_table' => 'civicrm_financial_item', 'return' => 'id'])) {
          civicrm_api3('EntityFinancialTrxn', 'create', [
            'id' => $eftID,
            'amount' => $submittedValues['amount_total'],
          ]);
        }
      }
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
