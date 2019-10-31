<?php
return array(
  array(
    'module' => 'biz.jmaconsulting.chreports',
    'name' => 'Donation History By Source (Summary)',
    'update' => 'never',
    'entity' => 'ReportInstance',
    'params' => array(
      'version' => 3,
      'report_id' => 'contribute/summary',
      'title' => ts('Donation History By Source (Summary)'),
      "description" => "Groups and totals contributions by criteria including contact, time period, financial type, contributor location, etc.",
      'permission' => 'access CiviReport',
      'is_active' => 1,
      "grouprole" => [
        "client administrator",
        "authenticated user",
        "administrator",
      ],
      "form_values" => "a:123:{s:8:\"entryURL\";s:87:\"https://canadahelps.civisite.com/civicrm/report/instance/27?reset=1&output=criteria\";s:6:\"fields\";a:2:{s:19:\"contribution_source\";s:1:\"1\";s:12:\"total_amount\";s:1:\"1\";}s:20:\"financial_account_op\";s:2:\"in\";s:23:\"financial_account_value\";a:0:{}s:21:\"receive_date_relative\";s:9:\"this.year\";s:17:\"receive_date_from\";s:0:\"\";s:15:\"receive_date_to\";s:0:\"\";s:22:\"thankyou_date_relative\";s:0:\"\";s:18:\"thankyou_date_from\";s:0:\"\";s:16:\"thankyou_date_to\";s:0:\"\";s:25:\"contribution_status_id_op\";s:2:\"in\";s:28:\"contribution_status_id_value\";a:1:{i:0;s:1:\"1\";}s:23:\"contribution_page_id_op\";s:2:\"in\";s:26:\"contribution_page_id_value\";a:0:{}s:11:\"currency_op\";s:2:\"in\";s:14:\"currency_value\";a:0:{}s:20:\"financial_type_id_op\";s:2:\"in\";s:23:\"financial_type_id_value\";a:0:{}s:16:\"total_amount_min\";s:0:\"\";s:16:\"total_amount_max\";s:0:\"\";s:15:\"total_amount_op\";s:3:\"lte\";s:18:\"total_amount_value\";s:0:\"\";s:25:\"non_deductible_amount_min\";s:0:\"\";s:25:\"non_deductible_amount_max\";s:0:\"\";s:24:\"non_deductible_amount_op\";s:3:\"lte\";s:27:\"non_deductible_amount_value\";s:0:\"\";s:13:\"total_sum_min\";s:0:\"\";s:13:\"total_sum_max\";s:0:\"\";s:12:\"total_sum_op\";s:3:\"lte\";s:15:\"total_sum_value\";s:0:\"\";s:15:\"total_count_min\";s:0:\"\";s:15:\"total_count_max\";s:0:\"\";s:14:\"total_count_op\";s:3:\"lte\";s:17:\"total_count_value\";s:0:\"\";s:13:\"total_avg_min\";s:0:\"\";s:13:\"total_avg_max\";s:0:\"\";s:12:\"total_avg_op\";s:3:\"lte\";s:15:\"total_avg_value\";s:0:\"\";s:14:\"campaign_id_op\";s:2:\"in\";s:17:\"campaign_id_value\";a:0:{}s:15:\"card_type_id_op\";s:2:\"in\";s:18:\"card_type_id_value\";a:0:{}s:10:\"amount_min\";s:0:\"\";s:10:\"amount_max\";s:0:\"\";s:9:\"amount_op\";s:3:\"lte\";s:12:\"amount_value\";s:0:\"\";s:22:\"soft_credit_type_id_op\";s:2:\"in\";s:25:\"soft_credit_type_id_value\";a:0:{}s:12:\"soft_sum_min\";s:0:\"\";s:12:\"soft_sum_max\";s:0:\"\";s:11:\"soft_sum_op\";s:3:\"lte\";s:14:\"soft_sum_value\";s:0:\"\";s:14:\"soft_count_min\";s:0:\"\";s:14:\"soft_count_max\";s:0:\"\";s:13:\"soft_count_op\";s:3:\"lte\";s:16:\"soft_count_value\";s:0:\"\";s:12:\"soft_avg_min\";s:0:\"\";s:12:\"soft_avg_max\";s:0:\"\";s:11:\"soft_avg_op\";s:3:\"lte\";s:14:\"soft_avg_value\";s:0:\"\";s:17:\"street_address_op\";s:3:\"has\";s:20:\"street_address_value\";s:0:\"\";s:14:\"postal_code_op\";s:3:\"has\";s:17:\"postal_code_value\";s:0:\"\";s:7:\"city_op\";s:3:\"has\";s:10:\"city_value\";s:0:\"\";s:13:\"country_id_op\";s:2:\"in\";s:16:\"country_id_value\";a:0:{}s:20:\"state_province_id_op\";s:2:\"in\";s:23:\"state_province_id_value\";a:0:{}s:12:\"county_id_op\";s:2:\"in\";s:15:\"county_id_value\";a:0:{}s:8:\"tagid_op\";s:2:\"in\";s:11:\"tagid_value\";a:0:{}s:6:\"gid_op\";s:2:\"in\";s:9:\"gid_value\";a:0:{}s:12:\"custom_25_op\";s:2:\"in\";s:15:\"custom_25_value\";a:0:{}s:18:\"custom_26_relative\";s:0:\"\";s:14:\"custom_26_from\";s:0:\"\";s:12:\"custom_26_to\";s:0:\"\";s:13:\"custom_14_min\";s:0:\"\";s:13:\"custom_14_max\";s:0:\"\";s:12:\"custom_14_op\";s:3:\"lte\";s:15:\"custom_14_value\";s:0:\"\";s:13:\"custom_15_min\";s:0:\"\";s:13:\"custom_15_max\";s:0:\"\";s:12:\"custom_15_op\";s:3:\"lte\";s:15:\"custom_15_value\";s:0:\"\";s:18:\"custom_16_relative\";s:0:\"\";s:14:\"custom_16_from\";s:0:\"\";s:12:\"custom_16_to\";s:0:\"\";s:18:\"custom_17_relative\";s:0:\"\";s:14:\"custom_17_from\";s:0:\"\";s:12:\"custom_17_to\";s:0:\"\";s:13:\"custom_18_min\";s:0:\"\";s:13:\"custom_18_max\";s:0:\"\";s:12:\"custom_18_op\";s:3:\"lte\";s:15:\"custom_18_value\";s:0:\"\";s:13:\"custom_19_min\";s:0:\"\";s:13:\"custom_19_max\";s:0:\"\";s:12:\"custom_19_op\";s:3:\"lte\";s:15:\"custom_19_value\";s:0:\"\";s:12:\"custom_28_op\";s:3:\"has\";s:15:\"custom_28_value\";s:0:\"\";s:9:\"group_bys\";a:1:{s:19:\"contribution_source\";s:1:\"1\";}s:14:\"group_bys_freq\";a:1:{s:12:\"receive_date\";s:5:\"MONTH\";}s:11:\"description\";s:121:\"Groups and totals contributions by criteria including contact, time period, contribution type, contributor location, etc.\";s:13:\"email_subject\";s:0:\"\";s:8:\"email_to\";s:0:\"\";s:8:\"email_cc\";s:0:\"\";s:9:\"row_count\";s:1:\"1\";s:13:\"is_navigation\";s:1:\"1\";s:9:\"view_mode\";s:4:\"view\";s:14:\"addToDashboard\";s:1:\"1\";s:13:\"cache_minutes\";s:2:\"60\";s:10:\"permission\";s:18:\"administer Reports\";s:9:\"parent_id\";s:2:\"29\";s:12:\"drilldown_id\";s:1:\"8\";s:8:\"radio_ts\";s:0:\"\";s:6:\"groups\";s:0:\"\";s:11:\"instance_id\";s:2:\"62\";s:10:\"navigation\";a:2:{s:2:\"id\";s:3:\"280\";s:9:\"parent_id\";s:2:\"29\";}}",
      'is_reserved' =>  0,
    ),
  ),
);
