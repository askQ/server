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
	if( empty($data->{'sessionid'}) || empty($data->{'title'})
	 	|| empty($data->{'typeid'}) ) {
		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
	    echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	$title = $data->{'title'} ;
	$typeid = $data->{'typeid'} ;


	//驗證參數格式,從sessionid中取出帳號資訊
	$array = split("_",$data->{'sessionid'}) ;
	$account = base64_decode($array[0]) ;

	//echo $account ; 
	

	//確認account,sessionid是否正確
	$sql = "select UserId"." from User where Account='"
		.$account."' and SessionId='".$data->{'sessionid'}."'" ;

	echo $sql."<br/>" ;

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

	
	//新增使用者發問的問題
    $sql = 	"insert into Question ".
			" SET UserId=".$userid.
			" ,Title='".$title."'".
			" ,BuildTime=now() " ;

	if( !empty($data->{'content'}) ) {
		$sql = $sql." ,Content=".$data->{'content'} ;
	}

	if( !empty($data->{'endtime'}) ) {
		$sql = $sql." ,EndTime='".$data->{'endtime'}."'" ;
	}


	//echo $sql."<br/>" ;

	if( !mysql_db_query($database,$sql,$link) ) {
	    $arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
	    include("./db/db_close.php") ;
	    return ;
	}


	//抓取問題代號,暫時抓最晚產生那筆,如果有兩個相同使用者同時發問,可能會出錯
	$sql =  "select QuestionId ".
			" from Question ".
			" where UserId=".$userid.
			" and Title='".$title."'".
		   	" order by BuildTime Desc "	;

	//echo $sql."<br/>" ;
	$result = mysql_db_query($database,$sql,$link) ;
	$row = mysql_fetch_assoc($result) ;
	//echo "<br/>".$row['QuestionId']."<br/>" ;

	$questionid = $row['QuestionId'] ;

		
	//新增問題與類型之對應
	$type_arr = explode( ',',$data->{'typeid'}) ;
	
	$sql =	"insert into QuestionType (TypeId,QuestionId) values " ;

	$sql =  $sql." (".trim($type_arr[0]).",".$questionid.")" ;

	$i = 1 ;

	for($i ; $i<count($type_arr) ; $i++) {
		$sql = $sql.",(".trim($type_arr[$i]).",".$questionid.")" ;
	}

	echo $sql."<br/>" ;
	

	if( !mysql_db_query($database,$sql,$link) ) {
		$arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}



	//回傳註冊成功訊息
	$arr = array('code'=>'0000','message'=>'question  success',) ;

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>

