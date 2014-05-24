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
	if( empty($data->{'sessionid'}) ) {
		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
	    echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	//驗證參數格式,從sessionid中取出帳號資訊
	$array = split("_",$data->{'sessionid'}) ;
	$account = base64_decode($array[0]) ;

	//echo $account ; 
	

	//確認account,sessionid是否正確
	$sql = "select Account,UserName,PicUrl,UserId"." from User where Account='"
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

	$account = $count[0] ;
	$name = $count[1] ;
	$picurl = $count[2] ;
	$userid = $count[3] ;


	//找出使用者發過的問題
    $sql = 	"select QuestionId,Title,BuildTime,FinishTime".
			" from Question".
			" where UserId=".$userid.
		   	" and IsValid=1 "	;

	//echo $sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$num = 0 ;
	$question = Array() ;

	while ($row = mysql_fetch_assoc($result)) {
		    $question[$num] = Array(
					"questioinid"=>$row['QuestionId'] ,
					"title"=>urlencode($row['Title']) ,
					"buildtime"=>$row['BuildTime'] ,
					"finishtime"=>$row['FinishTime'] 
			) ;
			$num++ ;
	}


	//回傳註冊成功訊息
	$arr = array('code'=>'0000','message'=>'register success',
			'account'=>$account,'name'=>urlencode($name),'picurl'=>urlencode($picurl),'question'=>$question) ;

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>

