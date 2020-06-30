<?php
/*
1. �������� � ����� �� �������� ���� � ������� ��� ��� � $file_path
2. �������� ��������� ������ ������� � ��������, ����������� � ������
*/



$file_path = '0445_2020000000_1C_test.txt';


$show_full_text = false;

//��� ���� �� ����� �������� � �������
$array_not_columns = array(
	'��������������' => '1'	
);

//��� ���� ������ ����������� � ����� � ������������� ������� ��������. ������������� ������� ��� ��� �����
$array_columns_as_text = array(
	'��������������' => '1'	
   ,'�����������������' => '1'	
   ,'��������������' => '1'	
   ,'�����������������' => '1'	
);

//��� ����(number) ������ ����������� � ���� ��� ���. ����� �������� � ��� ����� �� �������
$array_columns_as_russian_number = array(
	'�����' => '1'	
);


$file = file_get_contents($file_path);

//��� ������ ��������� � ����� ������
$file = str_replace(chr(13),'',$file);

//print $file;

//1. �������� ��� ����������� ����.����� � ������
$pattern = '/��������������.+�������������/sU';
$result = preg_match_all ( $pattern , $file , $account_sections);
$pattern_account = '/��������=(\d+)/';
foreach($account_sections[0] as $key => $account_section){
    $result = preg_match( $pattern_account , $account_section , $account_match);
    $account = $account_match[1];
	$accounts[$account] = $account;
}



//2. ������ �������

$pattern = '/��������������.+��������������/sU';

$result = preg_match_all ( $pattern , $file , $statements);

//print_r($statements);

//���������� ����� ������� ��� ������� 
foreach($statements[0] as $key => $statement){
	if($show_full_text)	$statements_arr[$key]['text']=$statement;
    $column_account_value = null;
	//�������� ������� �� ������
    $lines = explode ( chr(10) , $statement);
	foreach($lines as $line_num => $line){
		if(strpos($line,'=')===false){
		    $column_name = $line;
			$column_value = null;
		}else{
			$column_name = substr($line,0, strpos($line,'=' ) );
			$column_value = substr($line, strpos($line,'=' )+1 );
			if(   ($column_name=='��������������'  || $column_name=='��������������' )
			    && array_key_exists($column_value, $accounts) 
			  ){
				  if( $column_account_value )
				       $column_account_value .= chr(10).$column_value;
				  else $column_account_value = $column_value;
		    }	  
			if( array_key_exists($column_name, $array_columns_as_russian_number) ){
			    $column_value = str_replace('.',',',$column_value);
			}
			if( array_key_exists($column_name, $array_columns_as_text) ){
			    if(strlen($column_value)>0) $column_value = "'".$column_value;
			}	
		}
		if( ! array_key_exists ( $column_name , $array_not_columns ) )
	    $column_names[$column_name] = $column_name;
    	$statements_arr[$key][$column_name]=$column_value;
	}
    $statements_arr[$key]['self_account']="'".$column_account_value;
}

//������� ���� �������
print "<table border style='white-space:nowrap;'>
<tr>
<th>ID</th>
<th>'���' ����.����</th>
";
if($show_full_text) print "<td>text</td>";
foreach($column_names as $column_name)
    print "<th>$column_name</th>";
print "
</tr>
";

foreach($statements_arr as $key => $statement_array){
	print "<tr>
	<td>$key</td>
	<td>{$statement_array['self_account']}</td>
	";
	if($show_full_text) print "<td>{$statement_array['text']}</td>";
	foreach($column_names as $column_name){
		print "<td>{$statement_array[$column_name]}</td>";
	}	
	print "
	</tr>
	";
}



print "</table>";


?>