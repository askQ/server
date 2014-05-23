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


	//此API沒有必要的參數
	


	//依照條件找出問題,需注意的是問題要挑出獨立的
    $sql = "select distinct(Question.QuestionId),Question.Title,User.UserName".
			" from Question".
			" left join QuestionType on".
			" QuestionType.QuestionId=Question.QuestionId".
			" left join User on".
			" User.UserId=Question.UserId ".						
			" where 1=1 " ;

	if( !empty($data->{'typeid'}) ) {
		$sql = $sql." and QuestionType.TypeId in('".
		   str_replace(",","','",$data->{'typeid'})."')" ;
	}

	if( !empty($data->{'starttime'}) ) {
	    $sql = $sql." and Question.BuildTime > '".$data->{'starttime'}."'" ;
	}

	if( !empty($data->{'endtime'}) ) {
	    $sql = $sql." and Question.BuildTime < '".$data->{'endtime'}."'" ;
	}

	//echo $sql."<br/>" ;	
	
	$result = mysql_db_query($database,$sql,$link) ;

	$num = 0 ;
	$question = Array() ;

	while ($row = mysql_fetch_assoc($result)) {

		    $question[$num] = Array(
					"questioinid"=>$row['QuestionId'] ,
					"title"=>$row['Title'] ,
					"name"=>$row['UserName']
			) ;

			$num++ ;
	}


	$i = 0 ;
	//查詢該問題選項被選擇次數
	for($i ; $i<$num ; $i++) {
		$sql  = "select count(*)".
				" from UserChoice".
				" left join Choice on".
				" UserChoice.ChoiceId=Choice.ChoiceId".
				" left join Question on".
				" Choice.QuestionId = Question.QuestionId".
				" where Question.QuestionId=".$question[$i]['questioinid'] ;

		//echo $sql."<br/>" ;

		$result = mysql_db_query($database,$sql,$link) ;
		$row = mysql_fetch_assoc($result) ;

		//echo $row['count(*)']."<br/>" ;

		$question[$i]['num_click']=$row['count(*)'] ;
	}



    $i = 0 ;
    //查詢該問題訊息次數
    for($i ; $i<$num ; $i++) {
	    $sql  = "select count(*)".
				" from UserMessage".
				" where QuestionId=".$question[$i]['questioinid'] ;

		//echo $sql."<br/>" ;

		$result = mysql_db_query($database,$sql,$link) ;
		$row = mysql_fetch_assoc($result) ;

		//echo $row['count(*)']."<br/>" ;

		$question[$i]['num_message']=$row['count(*)'] ;
	}


	$i = 0 ;
    //查詢該問題所屬的類型
    for($i ; $i<$num ; $i++) {
	    $sql  = "select TypeId".
				" from QuestionType".
				" where QuestionId=".$question[$i]['questioinid'] ;
		
		//echo $sql."<br/>" ;

		$type = Array() ;
		
		$result = mysql_db_query($database,$sql,$link) ;
		while($row = mysql_fetch_assoc($result)) {
			array_push($type,$row['TypeId']) ;
		}

		$question[$i]['type'] = $type ;
		//var_dump($type) ;	
	}
	

	//回傳查詢成功訊息
	$arr = array('code'=>'0000','message'=>'query question success',
			'question'=>$question) ; 

	echo json_encode($arr)  ;	

	include("./db/db_close.php") ;

?>

