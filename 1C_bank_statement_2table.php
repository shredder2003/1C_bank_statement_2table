<?php
/*
1. положить в папку со скриптом файл и указать его им€ в $file_path
2. получить результат работы скрипта в браузере, скопировать в эксель
*/



$file_path = '0445_2020000000_1C_test.txt';


$show_full_text = false;

//эти пол€ не нужно включать в таблицу
$array_not_columns = array(
	' онецƒокумента' => '1'	
);

//эти пол€ эксель преобразует в число с отбрасыванием младших разр€дов. принудительно выведем это как текст
$array_columns_as_text = array(
	'ѕолучатель—чет' => '1'	
   ,'ѕлательщик орсчет' => '1'	
   ,'ѕлательщик—чет' => '1'	
   ,'ѕолучатель орсчет' => '1'	
);

//эти пол€(number) эксель преобразует в даты абы как. лучше заменить в них точку на зап€тую
$array_columns_as_russian_number = array(
	'—умма' => '1'	
);


$file = file_get_contents($file_path);

//все строки переводим в юникс формат
$file = str_replace(chr(13),'',$file);

//print $file;

//1. запомним все собственные банк.счета в массив
$pattern = '/—екци€–асч—чет.+ онец–асч—чет/sU';
$result = preg_match_all ( $pattern , $file , $account_sections);
$pattern_account = '/–асч—чет=(\d+)/';
foreach($account_sections[0] as $key => $account_section){
    $result = preg_match( $pattern_account , $account_section , $account_match);
    $account = $account_match[1];
	$accounts[$account] = $account;
}



//2. парсим выписку

$pattern = '/—екци€ƒокумент.+ онецƒокумента/sU';

$result = preg_match_all ( $pattern , $file , $statements);

//print_r($statements);

//сформируем имена колонок дл€ таблицы 
foreach($statements[0] as $key => $statement){
	if($show_full_text)	$statements_arr[$key]['text']=$statement;
    $column_account_value = null;
	//разобьЄм выписку на строки
    $lines = explode ( chr(10) , $statement);
	foreach($lines as $line_num => $line){
		if(strpos($line,'=')===false){
		    $column_name = $line;
			$column_value = null;
		}else{
			$column_name = substr($line,0, strpos($line,'=' ) );
			$column_value = substr($line, strpos($line,'=' )+1 );
			if(   ($column_name=='ѕолучатель—чет'  || $column_name=='ѕлательщик—чет' )
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

//выведем саму таблицу
print "<table border style='white-space:nowrap;'>
<tr>
<th>ID</th>
<th>'наш' банк.счЄт</th>
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