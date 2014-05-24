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
		|| empty($data->{'name'}) || empty($data->{'sex'})
		|| empty($data->{'email'}) || empty($data->{'birthtime'}) ) {
		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
	    echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	//驗證參數格式(skip)


	//確認account是否重複
	$sql = "select count(Account) from User where Account='"
		.$data->{'account'}."' and AccountType='01'" ;

	//echo $sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$count = mysql_fetch_row($result) ;

	//若大於0筆代表重覆
	if($count['0']>0) {
		$arr = array('code'=>'1101','message'=>'account is recurrent') ;
        echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	//產生sessionid,之後拉到函式內
	$sessionid = base64_encode($data->{'account'})."_".md5(rand()) ;
	

	//新增DB資料
	$sql = "insert into User (Account,Password,AccountType,UserName,".
			"Sex,Email,BuildTime,BirthTime,SessionId,PicUrl) values ('"
			.$data->{'account'}."','".$data->{'password'}."','01','"
			.$data->{'name'}."','".$data->{'sex'}."','".$data->{'email'}
			."',now(),'".$data->{'birthtime'}."','".$sessionid."'" ;

	if( !empty($data->{'pic'}) && !empty($data->{'extension'}) ) {
		$sql = $sql." , '"."/image/".$data->{'account'}.".".$data->{'extension'}."')" ;
	}
	else {
		$sql = $sql.", NULL)" ;
	}

	//echo $sql."<br/>" ;
	
	if( !mysql_db_query($database,$sql,$link) ) {
        $arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}
	

	//若有帶pic和extension參數則新增大頭圖片
	if( !empty($data->{'pic'}) && !empty($data->{'extension'}) ) {

		$pic =  base64_decode($data->{'pic'}) ;
		
		$handle = @fopen("./image/".$data->{'account'}.".".$data->{'extension'},"wb") ;

		//假如讀檔失敗回傳異常錯誤
		if(!$handle) {
			$arr = array('code'=>'0099','message'=>'unknow error') ;
	        echo json_encode($arr) ;
			include("./db/db_close.php") ;
			return ;
		}
		fwrite($handle,$pic) ;		
		fclose($handle);		
	}

	//回傳註冊成功訊息
	$arr = array('code'=>'0000','message'=>'register success',
			'sessionid'=>$sessionid) ;
	echo json_encode($arr) ;	

	include("./db/db_close.php") ;

?>

