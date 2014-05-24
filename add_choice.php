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
	if( empty($data->{'sessionid'}) || empty($data->{'questionid'}) ||
	 	empty($data->{'choice'})  ) {
		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
	    echo json_encode($arr) ;
		include("./db/db_close.php") ;
	    return ;
	}

	$questionid = $data->{'questionid'} ;
	$choice = $data->{'choice'} ;


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
	

	//新增選項資料
	$i=0 ;

	$sql =	"insert into Choice (QuestionId,Title,Content,PicUrl) values".
			" (".$questionid.",'".$choice[$i]->title."','".$choice[$i]->content."'"  ;

	if( !empty($choice[$i]->pic) && !empty($choice[$i]->extension) ) {
		$sql = $sql." , '/choice/".$questionid."_".$i.".".$choice[$i]->extension."'" ;
	}
	else {
		$sql = $sql." , NULL" ;
	}

	$sql = $sql.")" ;
	

	for($i=1 ; $i<count($choice) ; $i++) {
		$sql =	$sql." , (".$questionid.",'".$choice[$i]->title."','".$choice[$i]->content."'" ;

		if( !empty($choice[$i]->pic) && !empty($choice[$i]->extension) ) {
			$sql = $sql." , '/choice/".$questionid."_".$i.".".$choice[$i]->extension."'" ;
		}
		else {
			$sql = $sql." , NULL" ;
		}
		$sql = $sql.")" ;
	}

	//echo $sql."<br/>" ;

	if( !mysql_db_query($database,$sql,$link) ) {
		$arr = array('code'=>'0099','message'=>'unknow error') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}


	//如果choice有帶,pic和extension參數則新增大頭圖片
	for($i=0 ; $i<count($choice) ; $i++) {

		if( !empty($choice[$i]->pic) && !empty($choice[$i]->extension) ) {

			$pic =  base64_decode($choice[$i]->pic) ;

			$handle = @fopen("./choice/".$questionid."_".$i.".".$choice[$i]->extension,"wb") ;

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
	}

	//回傳新增選項完成
	$arr = array('code'=>'0000','message'=>'choice upload success') ;

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>
