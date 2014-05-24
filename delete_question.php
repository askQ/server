<?php

	include("./db/db_create.php") ;

	//未帶入data參數
	if( empty($_GET["data"]) ) {
		$arr = array('code'=>'0001','message'=>'you miss data') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}

	$data = json_decode($_GET["data"]) ;
	//var_dump($data) ;


	//判斷JSON格式是否正確
	if(empty($data)) {
		$arr = array('code'=>'0002','message'=>'json format is error') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}


	//確認必要參數是否到齊	
	if( empty($data->{'sessionid'}) || empty($data->{'questionid'}) ) {
		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
	    echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	$questionid = $data->{'questionid'} ;


	//驗證參數格式,從sessionid中取出帳號資訊
	$array = split("_",$data->{'sessionid'}) ;
	$account = base64_decode($array[0]) ;

	//echo $account ; 
	

	//確認account,sessionid是否正確
	$sql = "select UserId"." from User where Account='"
		.$account."' and SessionId='".$data->{'sessionid'}."'" ;

	//echo $sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$count = mysql_fetch_row($result) ;

	//var_dump($count) ;

	//若等於0筆代表找不到該身分
	if(!$count) {
		$arr = array('code'=>'0003','message'=>'sessioinid is wrong') ;
        echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	$userid = $count[0] ;

	
	//更改使用者發問的問題狀態
    $sql = 	" update Question ".			
			" set IsValid=0".
		   	" where QuestionId=".$questionid.
			" and UserId=".$userid	;

	//echo $sql."<br/>" ;

	if( !mysql_db_query($database,$sql,$link) ) {
	    $arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
	    include("./db/db_close.php") ;
	    return ;
	}

	//回傳刪除成功訊息
	$arr = array('code'=>'0000','message'=>'question delete success',) ;

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>
