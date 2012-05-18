<?php
$module_name = 'A1_Report';
$viewdefs [$module_name] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 'FIND_DUPLICATES',
        ),
      ),
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
          0 => 'description',
        ),
        2 => 
        array (
          0 => '',
          1 => '',
        ),
      ),
    ),
  ),
);
?>
<!-- script>
document.getElementById('DEFAULT').innerHTML=document.getElementById('DEFAULT').innerHTML+document.getElementById('fromphp').innerHTML+"<center><img src='/themes/Sugar5/images/Dropdown.gif'><a href='/public/exportcsv.php?id=<?php echo $_GET['record']; ?>' target='_blank'>Download EXCEL Report HERE</a>"+'</center>';
</script-->