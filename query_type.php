<?php

	include("./db/db_create.php") ;

	//該API不需要帶參數


	//找出資料庫目前的問題類型
    $sql = "select TypeId,TypeName".
			" from Type" ;

	//echo $sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$num = 0 ;
	$type = Array() ;

	while ($row = mysql_fetch_assoc($result)) {
		    $type[$num] = Array(
					"typeid"=>$row['TypeId'] ,
					"name"=>urlencode($row['TypeName'])
			) ;
			$num++ ;
	}

	//回傳查詢問題類型成功訊息
	$arr = array('code'=>'0000','message'=>'query type success',
			'type'=>$type) ;

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>

