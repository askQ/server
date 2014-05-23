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
    if( empty($data->{'questionid'}))  {

		$arr = array('code'=>'0003','message'=>'you miss paramemter') ;
		echo json_encode($arr) ;
		include("./db/db_close.php") ;
		return ;
	}


	//依照條件找出問題
    $sql =	"select Title,Content,ChoiceId,Command".
			" from Question".
			" where QuestionId=".$data->{'questionid'} ;
			
	//echo $sql."<br/>" ;

	$result = mysql_db_query($database,$sql,$link) ;

	$row = mysql_fetch_assoc($result) ;

	//var_dump($row) ;

	$title = $row['Title'] ;
	$content = $row['Content'] ;
	$choiceid = $row['ChoiceId'] ;
	$command = $row['Command'] ;


	//依照條件找出選項
    $sql =	"select ChoiceId,Title,Content,PicUrl".
			" from Choice".
			" where QuestionId=".$data->{'questionid'} ;

	//echo $sql."<br/>" ;		

	$result = mysql_db_query($database,$sql,$link) ;
	$num = 0 ;
	$choice = Array() ;	
	while ($row = mysql_fetch_assoc($result)) {
		    $choice[$num] = Array(
					"choiceid"=>$row['ChoiceId'] ,
					"title"=>$row['Title'] ,
					"content"=>$row['Content'],
					"picurl"=>$row['PicUrl']
			) ;
			$num++ ;
	}
	//var_dump($choice) ;


	//統計選擇選項的數目
	$i=0 ;
	for($i ; $i<$num ; $i++) {		
		$sql =  "select count(*)".
				" from UserChoice".
				" where ChoiceId=".$choice[$i]['choiceid'] ;

		//echo $sql."<br/>" ;

		$result = mysql_db_query($database,$sql,$link) ;		
		$row = mysql_fetch_assoc($result) ;
		$choice[$i]['num_click'] = $row['count(*)'] ;
	}
	
    //var_dump($choice) ;


	//統計男生選擇選項的數目
	$i=0 ;
    for($i ; $i<$num ; $i++) {
		$sql =  "select count(*)".
				" from UserChoice".
				" left join User on".
				" User.UserId=UserChoice.UserId".
				" where ChoiceId=".$choice[$i]['choiceid'].
			   	" and User.Sex='01'" ;

				//echo $sql."<br/>" ;				

				$result = mysql_db_query($database,$sql,$link) ;
				$row = mysql_fetch_assoc($result) ;
				$choice[$i]['num_boy'] = $row['count(*)'] ;
	}


    //統計女生選擇選項的數目
	$i=0 ;
	
	for($i ; $i<$num ; $i++) {
		$sql =  "select count(*)".
				" from UserChoice".
				" left join User on".
				" User.UserId=UserChoice.UserId".
				" where ChoiceId=".$choice[$i]['choiceid'].
				" and User.Sex='02'" ;
		
		//echo $sql."<br/>" ;
		
		$result = mysql_db_query($database,$sql,$link) ;
		$row = mysql_fetch_assoc($result) ;
		
		$choice[$i]['num_girl'] = $row['count(*)'] ;
	}

    //var_dump($choice) ;

	

	//回傳查詢成功訊息
	$arr = array('code'=>'0000','message'=>'query content success',
					'choiceid'=>$choiceid , 'command'=>$command,
					'choice'=>$choice) ; 

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>

