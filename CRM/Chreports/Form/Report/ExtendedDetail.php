<?php
use CRM_Chreports_ExtensionUtil as E;

class CRM_Chreports_Form_Report_ExtendedDetail extends CRM_Report_Form_Contribute_Detail {

  public function __construct() {
    parent::__construct();
    $tablename = E::getTableNameByName('Contribution_Details');
    $cpTableName = E::getTableNameByName('Campaign_Information');
    $this->_columns['civicrm_contribution']['group_bys']['contribution_page_id'] = ['title' => ts('Contribution Page')];
    $this->_columns['civicrm_contribution']['group_bys']['contribution_page_id'] = ['title' => ts('Contribution Page')];
    $this->_columns['civicrm_contribution']['order_bys']['contribution_page_id'] = ['title' => ts('Contribution Page'), 'dbAlias' => 'cp.title'];
    $this->_columns['civicrm_contribution']['order_bys']['source'] = ['title' => ts('Source')];
    $this->_columms['civicrm_contact']['fields']['exposed_id']['title'] = ts('Donor ID');
    $this->_columns['civicrm_contribution']['fields']['campaign_id'] = ['title' => ts('Campaign')];
    $this->_columns['civicrm_contribution']['order_bys']['campaign_id'] = ['title' => ts('Campaign'), 'dbAlias' => 'campaign.title'];
    if ($tableName) {
      if ($columnName = E::getColumnNameByName('Is_Receipted_')) {
        $this->_columns[$tableName]['fields']['receipt_type'] = [
          'title' => ts('Receipt Type'),
          'type' => CRM_Utils_Type::T_STRING,
          'dbAlias' => "
            CASE
              WHEN {$tablename}.{$columnName} = 1 AND contribution_civireport.source LIKE \'%CanadaHelps%\' THEN \'CanadaHelps\'
              WHEN {$tablename}.{$columnName} = 1 AND contribution_civireport.source NOT LIKE \'%CanadaHelps%\'  THEN \'Charity Issued\'
              ELSE NULL
            END
          ",
        ];
      }
    }
    if ($cpTableName) {
      $columnName = E::getColumnNameByName('Campaign_Type');
      $this->_columns['civicrm_contribution']['fields']['campaign_type'] = [
        'title' => ts('Contribution Page Type'),
        'type' => CRM_Utils_Type::T_STRING,
        'dbAlias' => "(SELECT $columnName FROM $cpTableName WHERE entity_id = contribution_civireport.contribution_page_id)",
      ];
      $this->_columns['civicrm_contribution']['filters']['campaign_type'] = [
        'title' => ts('Contribution Page Type'),
        'type' => CRM_Utils_Type::T_STRING,
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => CRM_Core_OptionGroup::values(E::getOptionGroupNameByColumnName($columnName)),
        'pseudofield' => TRUE,
        'dbAlias' => "(1)",
      ];
    }
  }

  public function from() {
    parent::from();
    $cpTableName = E::getTableNameByName('Campaign_Information');
    $this->_from .= "
      LEFT JOIN civicrm_contribution_page cp ON cp.id = contribution_civireport.contribution_page_id
      LEFT JOIN civicrm_campaign campaign ON campaign.id = contribution_civireport.campaign_id
    ";
    if (!empty($cpTableName)) {
      $filter = '';
      $join = 'LEFT';
      if (!empty($this->_params['campaign_type_value']) || in_array($this->_params['campaign_type_op'], ['nll', 'nnll'])) {
        $join = 'INNER';
        $field = [
          'dbAlias' => 'ct.' . E::getColumnNameByName('Campaign_Type'),
          'name' => 'campaign_type',
        ];
        $filter = "AND " . $var->whereClause($field, $params['campaign_type_op'], $params['campaign_type_value'], NULL, NULL);
      }

      $from .= "
      $join JOIN $cpTableName ct ON ct.entity_id = contribution_civireport.contribution_page_id $filter
      ";
    }
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {
    $key = $tableName . 'custom_' . CRM_Utils_Array::value('id', civicrm_api3('CustomField', 'get', ['sequential' => 1, 'name' => 'Receipt_Number'])['values'][0], '');
      if (!empty($object->_columnHeaders[$key])) {
        $column = [$key => $object->_columnHeaders[$key]];
        $object->_columnHeaders = $column + $object->_columnHeaders;
      }

      // reorder the columns
      $columnHeaders = [];
      foreach ([
        'civicrm_contribution_campaign_id',
        'civicrm_contact_exposed_id',
        'civicrm_contact_sort_name',
        'civicrm_contribution_receive_date',
        'civicrm_contribution_total_amount',
        'civicrm_contribution_financial_type_id',
        'civicrm_contribution_contribution_page_id',
        'civicrm_contribution_campaign_type',
        'civicrm_contribution_source',
        'civicrm_contribution_payment_instrument_id',
      ] as $name) {
        if (array_key_exists($name, $object->_columnHeaders)) {
          $columnHeaders[$name] = $object->_columnHeaders[$name];
          unset($object->_columnHeaders[$name]);
        }
      }
      $object->_columnHeaders = array_merge($object->_columnHeaders, $columnHeaders);

      if (!empty($object->_columnHeaders['civicrm_contribution_campaign_type'])) {
        $optionValues = CRM_Core_OptionGroup::values(E::getOptionGroupNameByColumnName(E::getColumnNameByName('Campaign_Type')));
        foreach ($var as $rowNum => $row) {
          $var[$rowNum]['civicrm_contribution_campaign_type'] = CRM_Utils_Array::value($row['civicrm_contribution_campaign_type'], $optionValues);
        }
      }
    }
    $entryFound = FALSE;
    $display_flag = $prev_cid = $cid = 0;
    $contributionTypes = CRM_Contribute_PseudoConstant::financialType();
    $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'label');
    $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();
    // We pass in TRUE as 2nd param so that even disabled contribution page titles are returned and replaced in the report
    $contributionPages = CRM_Contribute_PseudoConstant::contributionPage(NULL, TRUE);
    $batches = CRM_Batch_BAO_Batch::getBatches();
    foreach ($rows as $rowNum => $row) {
      if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
        // don't repeat contact details if its same as the previous row
        if (array_key_exists('civicrm_contact_id', $row)) {
          if ($cid = $row['civicrm_contact_id']) {
            if ($rowNum == 0) {
              $prev_cid = $cid;
            }
            else {
              if ($prev_cid == $cid) {
                $display_flag = 1;
                $prev_cid = $cid;
              }
              else {
                $display_flag = 0;
                $prev_cid = $cid;
              }
            }

            if ($display_flag) {
              foreach ($row as $colName => $colVal) {
                // Hide repeats in no-repeat columns, but not if the field's a section header
                if (in_array($colName, $this->_noRepeats) &&
                  !array_key_exists($colName, $this->_sections)
                ) {
                  unset($rows[$rowNum][$colName]);
                }
              }
            }
            $entryFound = TRUE;
          }
        }
      }

      if (CRM_Utils_Array::value('civicrm_contribution_contribution_or_soft', $rows[$rowNum]) ==
        'Contribution'
      ) {
        unset($rows[$rowNum]['civicrm_contribution_soft_soft_credit_type_id']);
      }

      $entryFound = $this->alterDisplayContactFields($row, $rows, $rowNum, 'contribution/detail', ts('View Contribution Details')) ? TRUE : $entryFound;
      // convert donor sort name to link
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        !empty($rows[$rowNum]['civicrm_contact_sort_name']) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact Summary for this Contact.");
      }

      if ($value = CRM_Utils_Array::value('civicrm_contribution_financial_type_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_financial_type_id'] = $contributionTypes[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_contribution_status_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_contribution_status_id'] = $contributionStatus[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_contribution_page_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_contribution_page_id'] = $contributionPages[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_payment_instrument_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = $paymentInstruments[$value];
        $entryFound = TRUE;
      }
      if (!empty($row['civicrm_batch_batch_id'])) {
        $rows[$rowNum]['civicrm_batch_batch_id'] = $batches[$row['civicrm_batch_batch_id']] ?? NULL;
        $entryFound = TRUE;
      }
      if (!empty($row['civicrm_financial_trxn_card_type_id'])) {
        $rows[$rowNum]['civicrm_financial_trxn_card_type_id'] = $this->getLabels($row['civicrm_financial_trxn_card_type_id'], 'CRM_Financial_DAO_FinancialTrxn', 'card_type_id');
        $entryFound = TRUE;
      }

      // Contribution amount links to viewing contribution
      if ($value = CRM_Utils_Array::value('civicrm_contribution_total_amount', $row)) {
        $rows[$rowNum]['civicrm_contribution_total_amount'] = CRM_Utils_Money::format($value, $row['civicrm_contribution_currency']);
        if (CRM_Core_Permission::check('access CiviContribute')) {
          $url = CRM_Utils_System::url(
            "civicrm/contact/view/contribution",
            [
              'reset' => 1,
              'id' => $row['civicrm_contribution_contribution_id'],
              'cid' => $row['civicrm_contact_id'],
              'action' => 'view',
              'context' => 'contribution',
              'selectedChild' => 'contribute',
            ],
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_contribution_total_amount_link'] = $url;
          $rows[$rowNum]['civicrm_contribution_total_amount_hover'] = ts("View Details of this Contribution.");
        }
        $entryFound = TRUE;
      }

      // convert campaign_id to campaign title
      if (array_key_exists('civicrm_contribution_campaign_id', $row)) {
        if ($value = $row['civicrm_contribution_campaign_id']) {
          $rows[$rowNum]['civicrm_contribution_campaign_id'] = $this->campaigns[$value];
          $entryFound = TRUE;
        }
      }

      // soft credits
      if (array_key_exists('civicrm_contribution_soft_credits', $row) &&
        'Contribution' ==
        CRM_Utils_Array::value('civicrm_contribution_contribution_or_soft', $rows[$rowNum]) &&
        array_key_exists('civicrm_contribution_contribution_id', $row)
      ) {
        $query = "
SELECT civicrm_contact_id, civicrm_contact_sort_name, civicrm_contribution_total_amount, civicrm_contribution_currency
FROM   {$this->temporaryTables['civireport_contribution_detail_temp2']['name']}
WHERE  civicrm_contribution_contribution_id={$row['civicrm_contribution_contribution_id']}";
        $dao = CRM_Core_DAO::executeQuery($query);
        $string = '';
        $separator = ($this->_outputMode !== 'csv') ? "<br/>" : ' ';
        while ($dao->fetch()) {
          $url = CRM_Utils_System::url("civicrm/contact/view", 'reset=1&cid=' .
            $dao->civicrm_contact_id);
          $string = $string . ($string ? $separator : '') .
            "<a href='{$url}'>{$dao->civicrm_contact_sort_name}</a> " .
            CRM_Utils_Money::format($dao->civicrm_contribution_total_amount, $dao->civicrm_contribution_currency);
        }
        $rows[$rowNum]['civicrm_contribution_soft_credits'] = $string;
      }

      if (array_key_exists('civicrm_contribution_soft_credit_for', $row) &&
        'Soft Credit' ==
        CRM_Utils_Array::value('civicrm_contribution_contribution_or_soft', $rows[$rowNum]) &&
        array_key_exists('civicrm_contribution_contribution_id', $row)
      ) {
        $query = "
SELECT civicrm_contact_id, civicrm_contact_sort_name
FROM   {$this->temporaryTables['civireport_contribution_detail_temp1']['name']}
WHERE  civicrm_contribution_contribution_id={$row['civicrm_contribution_contribution_id']}";
        $dao = CRM_Core_DAO::executeQuery($query);
        $string = '';
        while ($dao->fetch()) {
          $url = CRM_Utils_System::url("civicrm/contact/view", 'reset=1&cid=' .
            $dao->civicrm_contact_id);
          $string = $string .
            "\n<a href='{$url}'>{$dao->civicrm_contact_sort_name}</a>";
        }
        $rows[$rowNum]['civicrm_contribution_soft_credit_for'] = $string;
      }

      // CRM-18312 - hide 'contribution_or_soft' column if unchecked.
      if (!empty($this->noDisplayContributionOrSoftColumn)) {
        unset($rows[$rowNum]['civicrm_contribution_contribution_or_soft']);
        unset($this->_columnHeaders['civicrm_contribution_contribution_or_soft']);
      }

      //convert soft_credit_type_id into label
      if (array_key_exists('civicrm_contribution_soft_soft_credit_type_id', $rows[$rowNum])) {
        $rows[$rowNum]['civicrm_contribution_soft_soft_credit_type_id'] = CRM_Core_PseudoConstant::getLabel(
          'CRM_Contribute_BAO_ContributionSoft',
          'soft_credit_type_id',
          $row['civicrm_contribution_soft_soft_credit_type_id']
        );
      }

      // Contribution amount links to viewing contribution
      if ($value = CRM_Utils_Array::value('civicrm_pledge_payment_pledge_id', $row)) {
        if (CRM_Core_Permission::check('access CiviContribute')) {
          $url = CRM_Utils_System::url(
            "civicrm/contact/view/pledge",
            [
              'reset' => 1,
              'id' => $row['civicrm_pledge_payment_pledge_id'],
              'cid' => $row['civicrm_contact_id'],
              'action' => 'view',
              'context' => 'pledge',
              'selectedChild' => 'pledge',
            ],
            $this->_absoluteUrl
          );
          $rows[$rowNum]['civicrm_pledge_payment_pledge_id_link'] = $url;
          $rows[$rowNum]['civicrm_pledge_payment_pledge_id_hover'] = ts("View Details of this Pledge.");
        }
        $entryFound = TRUE;
      }

      $entryFound = $this->alterDisplayAddressFields($row, $rows, $rowNum, 'contribute/detail', 'List all contribution(s) for this ') ? TRUE : $entryFound;

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
      $lastKey = $rowNum;
    }
  }

}
