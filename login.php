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
	if( empty($data->{'account'}) || empty($data->{'password'})
		|| empty($data->{'type'}) )  {
		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
	    echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	//驗證參數格式(skip)


	//確認帳密是否正確
	$sql = "select count(Account) from User where Account='"
		.$data->{'account'}."' and AccountType='".$data->{'type'}.
		"' and Password='".$data->{'password'}."'" ;

	//echo "<br/>".$sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$count = mysql_fetch_row($result) ;

	//若等於0筆代表找不到,帳密有誤
	if($count['0']==0) {
		$arr = array('code'=>'1201','message'=>'account or password is wrong') ;
        echo json_encode($arr) ;
		include("./db/db_close.php") ;		
	    return ;
	}

	//產生sessionid,之後拉到函式內
	$sessionid = base64_encode($data->{'account'})."_".md5(rand()) ;
	

	//更新DB資料	
	$sql = "update User set SessionId='".$sessionid."',AccessTime=now() ".
			" where Account='".$data->{'account'}."' and AccountType='".
			$data->{'type'}."' and Password='".$data->{'password'}."'" ;
	

	echo $sql."<br/>" ;

	
	if( !mysql_db_query($database,$sql,$link) ) {
        $arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}
	

	//回傳登入成功訊息
	$arr = array('code'=>'0000','message'=>'register success',
			'sessionid'=>$sessionid) ;
	echo json_encode($arr) ;	

	include("./db/db_close.php") ;

?>

