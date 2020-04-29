<?php
use CRM_Chreports_ExtensionUtil as E;

class CRM_Chreports_Form_Report_GLSummaryReport extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = ['Contribute'];

  protected $_groupByDateFreq = [
    'MONTH' => 'Month',
    'YEARWEEK' => 'Week',
    'DATE' => 'Day',
    'QUARTER' => 'Quarter',
    'YEAR' => 'Year',
  ];

  protected $_customGroupGroupBy = FALSE; function __construct() {
    parent::__construct();
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'sort_name' => array(
            'title' => E::ts('Donor Name'),
            'no_repeat' => TRUE,
          ),
          'contact_id' => array(
            'title' => ts('Donor ID'),
            'dbAlias' => 'contribution_civireport.contact_id',
          ),
        ),
        'filters' => array(
          'id' => array(
            'no_display' => TRUE,
          ),
        ),
      ),
      'civicrm_contribution' => [
        'dao' => 'CRM_Contribute_BAO_Contribution',
        'fields' => [
          'id' => [
            'title' => E::ts('Contribution ID'),
          ],
          'gl_account' => [
            'title' => E::ts('Financial Account'),
            'required' => TRUE,
            'dbAlias' => 'fa.name',
          ],
          'gl_account_code' => [
            'title' => E::ts('GL Code'),
            'default' => TRUE,
            'dbAlias' => 'fa.accounting_code',
          ],
          'gl_account_type' => [
            'title' => E::ts('Financial Account Type'),
            'dbAlias' => 'fa.financial_account_type_id',
          ],
          'count' => [
            'title' => E::ts('Number of Donations'),
            'type' => CRM_Utils_TYPE::T_INT,
            'dbAlias' => 'COUNT(DISTINCT contribution_civireport.id)',
          ],
          'gl_amount' => [
            'title' => E::ts('Total Amount'),
            'default' => TRUE,
            'type' => CRM_Utils_TYPE::T_MONEY,
            'dbAlias' => 'SUM(temp.amount)'
          ],
          'receive_date' => [
            'title' => E::ts('Received Date'),
            'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          ],
          'financial_type_id' => [
            'title' => E::ts('Fund'),
          ],
          'payment_instrument_id' => [
            'title' => E::ts('Payment Method'),
          ],
          'source' => [
            'title' => E::ts('Source'),
          ],
          'credit_card_type_id' => [
            'title' => E::ts('Credit Card Type'),
            'type' => CRM_Utils_TYPE::T_INT,
            'dbAlias' => 'temp.card_type_id',
          ],
        ],
        'filters' => [
          'contribution_id' => array(
            'title' => 'Contribution ID',
            'type' => CRM_Utils_Type::T_INT,
            'dbAlias' => 'contribution_civireport.id'
          ),
          'receive_date' => [
            'title' => E::ts('Receive Date'),
            'operatorType' => CRM_Report_form::OP_DATETIME,
            'type' => CRM_Utils_TYPE::T_DATE + CRM_Utils_Type::T_TIME,
          ],
          'contribution_status_id' => [
            'title' => ts('Contribution Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_BAO_Contribution::buildOptions('contribution_status_id', 'search'),
            'default' => [1],
            'type' => CRM_Utils_Type::T_INT,
          ],
          'gl_account' => [
            'title' => ts('Financial Account'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::financialAccount(),
            'dbAlias' => 'fa.id',
          ],
          'financial_type_id' => [
            'title' => ts('Fund'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Financial_BAO_FinancialType::getAvailableFinancialTypes(),
            'type' => CRM_Utils_Type::T_INT,
          ],
          'payment_instrument_id' => [
            'title' => ts('Payment Method'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::paymentInstrument(),
            'type' => CRM_Utils_Type::T_INT,
          ],
          'credit_card_type_id' => [
            'title' => E::ts('Credit Card Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Financial_DAO_FinancialTrxn::buildOptions('card_type_id'),
            'dbAlias' => 'temp.card_type_id',
          ],
        ],
        'order_bys' => [
          'receive_date' => [
            'title' => E::ts('Received Date'),
          ],
        ],
        'group_bys' => [
          'gl_account' => [
            'title' => E::ts('Financial Account'),
            'default' => TRUE,
            'required' => TRUE,
          ],
          'contact_id' => [
            'title' => ts('Donor ID'),
          ],
          'sort_name' => array(
            'title' => E::ts('Donor Name'),
          ),
          'receive_date' => [
            'title' => E::ts('Received Date'),
            'frequency' => TRUE,
          ],
          'financial_type_id' => [
            'title' => E::ts('Fund'),
          ],
          'payment_instrument_id' => [
            'title' => ts('Payment Method'),
          ],
          'credit_card_type_id' => [
            'title' => E::ts('Credit Card Type'),
          ],
          'source' => [
            'title' => E::ts('Source'),
          ],
          'id' => [
            'title' => ts('Contribution ID'),
          ],
        ],
        'grouping' => 'contribute-fields',
      ],
    );
  }

  function select() {
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            if ($fieldName == 'receive_date' && !empty($this->_params['group_bys'][$fieldName])) {
              switch ($this->_params['group_bys_freq'][$fieldName]) {
                case 'YEAR':
                  $field['dbAlias'] = "YEAR({$field['dbAlias']})";
                  break;

                case 'QUARTER':
                  $field['dbAlias'] = "YEAR({$field['dbAlias']}), QUARTER({$field['dbAlias']})";
                  break;

                case 'YEARWEEK':
                  $field['dbAlias'] = "YEARWEEK({$field['dbAlias']})";
                  break;

                case 'MONTH':
                  $field['dbAlias'] = "EXTRACT(YEAR_MONTH FROM {$field['dbAlias']})";
                  break;

                case 'DATE':
                  $field['dbAlias'] = "DATE({$field['dbAlias']})";
                  break;
              }
            }
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  // core doesn't support 'required' attribute for group_bys field, so we are overriding the core function to make 'GL Account' as required grouping field
  public function addGroupBys() {
    parent::addGroupBys();
    $obj = $this->getElementFromGroup("group_bys", 'gl_account');
    if ($obj) {
     $obj->freeze();
    }
  }

  function from() {
    $financialAccountTypes = CRM_Core_OptionGroup::values('financial_account_type', FALSE, FALSE, FALSE, NULL, 'name');
    $revenue = array_search('Revenue', $financialAccountTypes);
    $expense = array_search('Expenses', $financialAccountTypes);

    $sql = "
    SELECT SQ1.contribution_id, SQ1.financial_account_id, fa.financial_account_type_id, SUM(SQ1.total_amount) as amount, SQ1.card_type_id
    FROM (
        select eftc.entity_id as contribution_id, if(ft.from_financial_account_id is null, fi.financial_account_id, ft.from_financial_account_id) as financial_account_id, ft.total_amount, ft.card_type_id
        FROM
        civicrm_entity_financial_trxn eftc
        inner join civicrm_entity_financial_trxn efti on eftc.entity_table='civicrm_contribution' AND efti.entity_table='civicrm_financial_item' AND eftc.financial_trxn_id=efti.financial_trxn_id
        inner join civicrm_financial_trxn ft on eftc.financial_trxn_id=ft.id inner join
        civicrm_financial_item fi on efti.entity_id=fi.id
        inner join civicrm_financial_account fa on fi.financial_account_id = fa.id AND fa.financial_account_type_id = $revenue

    UNION ALL
        select eftc.entity_id as contribution_id, ft.to_financial_account_id AS financial_account_id, ft.total_amount, ft.card_type_id
        FROM civicrm_entity_financial_trxn eftc
        inner join civicrm_entity_financial_trxn efti on eftc.entity_table='civicrm_contribution' AND efti.entity_table='civicrm_financial_item' AND eftc.financial_trxn_id=efti.financial_trxn_id
        inner join civicrm_financial_trxn ft on eftc.financial_trxn_id=ft.id
        inner join civicrm_financial_item fi on efti.entity_id=fi.id
        inner join civicrm_financial_account fa on fi.financial_account_id = fa.id AND fa.financial_account_type_id = $expense

    ) AS SQ1
     INNER join civicrm_financial_account fa on SQ1.financial_account_id = fa.id AND fa.financial_account_type_id IN ($revenue, $expense)
    GROUP BY contribution_id, financial_account_id
    HAVING SUM(SQ1.total_amount) <> 0
    ";

    $this->createTemporaryTable('financial_revenue_details', $sql);
    $this->createTemporaryTable('financial_expense_details', " SELECT * FROM {$this->temporaryTables['financial_revenue_details']['name']} WHERE financial_account_type_id = $expense ");
    CRM_Core_DAO::executeQuery(" DELETE FROM {$this->temporaryTables['financial_revenue_details']['name']} WHERE financial_account_type_id = $expense ");
    CRM_Core_DAO::executeQuery("
      UPDATE {$this->temporaryTables['financial_revenue_details']['name']} temp1
      INNER JOIN {$this->temporaryTables['financial_expense_details']['name']} temp2  ON temp2.contribution_id = temp1.contribution_id
      SET temp1.amount = (temp1.amount - temp2.amount)
    ");

    $this->_from = "
         FROM  civicrm_contact {$this->_aliases['civicrm_contact']}
               INNER JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
                          ON {$this->_aliases['civicrm_contact']}.id =
                             {$this->_aliases['civicrm_contribution']}.contact_id
          INNER JOIN (
            (SELECT * FROM {$this->temporaryTables['financial_revenue_details']['name']})
            UNION
            (SELECT * FROM {$this->temporaryTables['financial_expense_details']['name']})
         ) as temp ON temp.contribution_id = {$this->_aliases['civicrm_contribution']}.id AND {$this->_aliases['civicrm_contribution']}.is_test = 0
         INNER JOIN civicrm_financial_account fa on temp.financial_account_id = fa.id
    ";
  }

  function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          }
          else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    if (empty($clauses)) {
      $this->_where = "WHERE ( 1 ) ";
    }
    else {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }
  }

  function groupBy() {
    $groupBys = [];
    $params = CRM_Utils_Array::value('group_bys', $this->_params);
    if (!empty($params)) {
      foreach ($params as $groupBy => $dontCare) {
        $alias = $groupBy == 'sort_name' ? $this->_aliases['civicrm_contact'] : $this->_aliases['civicrm_contribution'];
        if ($groupBy == 'gl_account') {
          $groupBys[] = 'fa.name';
        }
        elseif ($groupBy == 'credit_card_type_id') {
          $groupBys[] = 'temp.card_type_id';
        }
        elseif ($groupBy == 'receive_date') {
          $table = $this->_columns['civicrm_contribution'];

          if (!empty($table['group_bys'][$groupBy]['frequency']) &&
            !empty($this->_params['group_bys_freq'][$groupBy])
          ) {
            switch ($this->_params['group_bys_freq'][$groupBy]) {
              case 'YEAR':
                $groupBys[] = " YEAR({$alias}.{$groupBy})";
                break;

              case 'QUARTER':
                $groupBys[] = "YEAR({$alias}.{$groupBy}), QUARTER({$alias}.{$groupBy})";
                break;

              case 'YEARWEEK':
                $groupBys[] = "YEARWEEK({$alias}.{$groupBy})";
                break;

              case 'MONTH':
                $groupBys[] = "EXTRACT(YEAR_MONTH FROM {$alias}.{$groupBy})";
                break;

              case 'DATE':
                $groupBys[] = "DATE({$alias}.{$groupBy})";
                break;
            }
          }
        }
        else {
          $groupBys[] = $alias . '.' . $groupBy;
        }
      }
    }
    $this->_groupBy = " GROUP BY " . implode(', ', $groupBys);
  }

  function orderBy() {
    $this->storeOrderByArray();
    if (!empty($this->_orderByArray)) {
      $this->_orderBy = "ORDER BY " . implode(', ', $this->_orderByArray);
    }
    else {
      $this->_orderBy = " ORDER BY fa.name ASC";
    }
  }

  public function statistics(&$rows) {
      $statistics = parent::statistics($rows);
      $sql = "
      SELECT
      COUNT(DISTINCT {$this->_aliases['civicrm_contribution']}.id) as total_count,
      SUM(temp.amount) as amount,
      {$this->_aliases['civicrm_contribution']}.currency

       {$this->_from} {$this->_where} GROUP BY {$this->_aliases['civicrm_contribution']}.currency
      ";
      $dao = CRM_Core_DAO::executeQuery($sql);
      $amount = [];
      $count = 0;
      while ($dao->fetch()) {
       $amount[$dao->currency] = CRM_Utils_Money::format($dao->amount, $dao->currency) . " ($dao->total_count)";
       $count += $dao->total_count;
     }

     $statistics['counts']['count'] = [
       'value' => $count,
       'title' => ts('Total Contributions'),
       'type' => CRM_Utils_Type::T_STRING,
     ];
     $statistics['counts']['amount'] = [
       'value' => implode(', ', $amount),
       'title' => ts('Total Amount'),
       'type' => CRM_Utils_Type::T_STRING,
     ];

     $columnHeaders = [];
     foreach ([
       'civicrm_contribution_gl_account',
       'civicrm_contribution_gl_account_code',
       'civicrm_contribution_gl_account_type',
       'civicrm_contact_contact_id',
       'civicrm_contact_sort_name',
       'civicrm_contribution_receive_date',
       'civicrm_contribution_completed_contributions',
       'civicrm_contribution_gl_amount',
       'civicrm_contribution_financial_type_id',
       'civicrm_contribution_payment_instrument_id',
       'civicrm_contribution_credit_card_type_id',
       'civicrm_contribution_source',
       'civicrm_contribution_id',
       'civicrm_contribution_count',
     ] as $name) {
       if (array_key_exists($name, $this->_columnHeaders)) {
         $columnHeaders[$name] = $this->_columnHeaders[$name];
         unset($this->_columnHeaders[$name]);
       }
     }
     $this->_columnHeaders = array_merge($columnHeaders, $this->_columnHeaders);

     return $statistics;
  }

  function alterDisplay(&$rows) {
    $paymentInstruments = CRM_Core_OptionGroup::values('payment_instrument', FALSE, FALSE, FALSE, NULL, 'label');
    $financialType = CRM_Contribute_PseudoConstant::financialType(NULL, FALSE);
    $financialAccountTypes = CRM_Core_OptionGroup::values('financial_account_type', FALSE, FALSE, FALSE, NULL, 'name');
    $ccTypes = CRM_Financial_DAO_FinancialTrxn::buildOptions('card_type_id');

    // custom code to alter rows
    $checkList = array();
    foreach ($rows as $rowNum => $row) {

      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        $rows[$rowNum]['civicrm_contact_sort_name'] &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = E::ts("View Contact Summary for this Contact.");
      }

      if (!empty($row['civicrm_contribution_financial_type_id'])) {
        $rows[$rowNum]['civicrm_contribution_financial_type_id'] = $financialType[$row['civicrm_contribution_financial_type_id']];
      }
      if (!empty($row['civicrm_contribution_payment_instrument_id'])) {
        $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = $paymentInstruments[$row['civicrm_contribution_payment_instrument_id']];
      }
      if (!empty($row['civicrm_contribution_gl_account_type'])) {
        $rows[$rowNum]['civicrm_contribution_gl_account_type'] = $financialAccountTypes[$row['civicrm_contribution_gl_account_type']];
      }
      if (!empty($row['civicrm_contribution_credit_card_type_id'])) {
        $rows[$rowNum]['civicrm_contribution_credit_card_type_id'] = $ccTypes[$row['civicrm_contribution_credit_card_type_id']];
      }
    }
  }

}
