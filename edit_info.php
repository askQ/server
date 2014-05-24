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
	$sql = "select UserId,Account"." from User where Account='"
		.$account."' and SessionId='".$data->{'sessionid'}."'" ;

	echo $sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$row = mysql_fetch_assoc($result) ;
	$userid = $row['UserId'] ;
	$account =  $row['Account'] ;
	


	//若等於0筆代表找不到該身分
	if(!$userid) {
		$arr = array('code'=>'0003','message'=>'sessioinid is wrong') ;
        echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}


	//小技巧,故意寫入無意義的給值省去後面parse的麻煩
	$sql = "update User set BuildTime=User.BuildTime " ;
	
	if( !empty($data->{'name'})) {
		$sql = $sql.", UserName='".$data->{'name'}."'" ;
	}

	if( !empty($data->{'password'})) {
		$sql = $sql.", Password='".$data->{'password'}."'" ;
    }

    if( !empty($data->{'sex'})) {
		$sql = $sql.", Sex='".$data->{'sex'}."'" ;
    }

	if( !empty($data->{'email'})) {
		$sql = $sql.", Email='".$data->{'email'}."'" ;
    }

	if( !empty($data->{'birthtime'})) {
		$sql = $sql.", BirthTime='".$data->{'birthtime'}."'" ;
    }

	if( !empty($data->{'pic'}) && !empty($data->{'extension'}) ) {
		$sql=$sql.",PicUrl='"."/image/".$account.".".$data->{'extension'}."'" ;
	}

	$sql = $sql." where UserId=".$userid ;

	//echo $sql."<br/>" ;

	if( !mysql_db_query($database,$sql,$link) ) {
		$arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
    }

	//若有帶pic和extension參數則新增大頭圖片,缺少更新資料庫路徑(待補)
    if( !empty($data->{'pic'}) && !empty($data->{'extension'}) ) {
	    $pic =  base64_decode($data->{'pic'}) ;
	    $handle = @fopen("./image/".$account.".".$data->{'extension'},"wb") ;

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
	

	//回傳修改個人資料成功訊息
	$arr = array('code'=>'0000','message'=>'update success') ;

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>

