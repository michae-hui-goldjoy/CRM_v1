<?php
$module_name = 'A1_Report';
$viewdefs [$module_name] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 'name',
          1 => 
          array (
            'name' => 'reporttype',
            'studio' => 'visible',
            'label' => 'LBL_REPORTTYPE',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'company_field',
            'studio' => 'visible',
            'label' => 'LBL_COMPANY_FIELD',
          ),
          1 => 
          array (
            'name' => 'voter_field',
            'studio' => 'visible',
            'label' => 'LBL_VOTER_FIELD',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'rpt_assigned_user',
            'studio' => 'visible',
            'label' => 'LBL_RPT_ASSIGNED_USER',
          ),
          1 => '',
        ),
        3 => 
        array (
          0 => 'description',
        ),
      ),
    ),
  ),
);
?>
