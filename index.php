<html>
<head>
<title></title>
<link href="css_sp.css" rel="stylesheet" type="text/css">
<style type="text/css">
html, body {
	margin: 0;
	text-align: center;
}

body {
	background-image: url(drawable/back.png);
	background-repeat: repeat-x;
}
</style>
<script language="javascript">
function edit(f_account,carry_date,c_id){
	//alert(carry_date);
	var id = "<?php echo $_GET['id'];?>";
	var pur="<?php echo $_GET['pur'];?>";
	
	if(id != f_account || pur != 1){
		alert('只有相關人員能編輯');
	}
	else{
		if(carry_date=='1'){
			location.href="crop.php?id=<?php echo $_GET['id'];?>&pur=<?php echo $_GET['pur'];?>&c_id="+c_id+"";
		}else{
			alert('你已收成，無法修改');
		}
	}
}
function add() {
		var id = "<?php echo $_GET['id'];?>";
		var pur="<?php echo $_GET['pur'];?>";
		if(id.match('null') || id == null || id==""){
			alert('請登入');
			location.href='login.php';
		}else if( pur!=1){
			alert('你不是農夫，沒有操作權限。');
		}else{
			location.href="addfruit.php?id=<?php echo $_GET['id'];?>&pur=<?php echo $_GET['pur'];?>";
		}
		/*if(<?php echo $_GET['pur'];?> ==1){
			location.href="addfruit.php?id=<?php echo $_GET['id'];?>&pur=<?php echo $_GET['pur'];?>";
			
		}else if(<?php echo $_GET['pur'];?> >1){
			alert('你不是農夫，沒有操作權限。');
		}else{
			alert('請登入');
			location.href='login.php';
		}*/
	
		
		
		
	}
function buy_item(c_id) {							//新增購買的訂單
	var id="<?php echo $_GET['id']?>";				
	var pur="<?php echo $_GET['pur']?>";
	if(pur==2){
		var amount = prompt("訂購數量", 1);
		
		if(amount!=null){
			alert("訂購數量:   "+amount);
			window.location.href="index.php?id="+id+"&pur="+pur+"&amount="+amount+"&c_id="+c_id+"";
			var error="<?php
				require 'database.php';
				$stmt2=$conn->prepare('select amount,price from crop_record where crop_id=:c_id');
				$stmt2->bindValue(':c_id',$_GET['c_id']);
				$stmt2->execute();
				$crop_record  = $stmt2->fetch(PDO::FETCH_OBJ);
				$price=$crop_record->price;
				$f_amount=$crop_record->amount;
				if($_GET['amount']>$f_amount){
					echo"error";
				}else{
					date_default_timezone_set('Asia/Taipei');
					$stmt = $conn->prepare('insert into sales_record(c_account,check_date,amount,accepted,total,crop_id)values(:c_account, :check_date, :amount, :accepted, :total,:crop_id)');
					$stmt->bindValue(':c_account', $_GET['id']);
					$stmt->bindValue(':check_date', date('Y-m-d H:i:s', time()) );
					$stmt->bindValue(':amount',$_GET['amount']);
					$stmt->bindValue(':accepted', false );
					$stmt->bindValue(':total',  $_GET['amount']*$price);
					$stmt->bindValue(':crop_id',$_GET['c_id'] );
					$stmt->execute();
					//做購買數量動作
					$number=$f_amount-$_GET['amount'];
					if($number>=0){			//有剩餘的話做修改
						$stmt3 = $conn->prepare('update crop_record set amount=:number where crop_id=:c_id');
						$stmt3->bindValue(':c_id',$_GET['c_id']);
						$stmt3->bindValue(':number',$number);
						$stmt3->execute();
					}
					
				}
			?>";
			if(error=="error"){
				alert("請輸入適當數量!!");
			}
			
		}else{
			alert("請輸入數量");
		}
	}else{
		alert("你不是民眾，無法進行購買");
	}
	
		
}
</script>


<?php
require 'database.php';
//預設每筆頁數(依需求修改)
$pageRow_records = 8;
//預設頁數
$num_pages = 1;
//若已經有翻頁，將頁數更新
if (isset($_GET['page'])) {
  $num_pages = $_GET['page'];
}
//本頁開始記錄筆數 = (頁數-1)*每頁記錄筆數
$startRow_records = ($num_pages - 1) * $pageRow_records;
//加上限制顯示筆數的SQL敘述句，由本頁開始記錄筆數開始，每頁顯示預設筆數

$sql_query_limit = $conn->prepare('select crop_id,vf_name,amount,price,name,crop_date,carry_date,willcarry_date,crop_record.f_account  from crop_record , farmer , vegetable_fruit where crop_record.vf_id=vegetable_fruit.vf_id and crop_record.f_account=farmer.account  LIMIT '.$startRow_records.','.$pageRow_records);
$sql_query_limit->execute();

//計算總筆數
$stmt = $conn->prepare('select count(*) from crop_record');
$stmt->execute();
$total_records = $stmt-> fetchColumn();

//計算總頁數=(總筆數/每頁筆數)後無條件進位。
$total_pages = ceil($total_records/$pageRow_records);

//$stmt = $conn->prepare('select * from member');
//$stmt->execute();
?>

<meta http-equiv="Content-Type" content="text/html;">
</head>

<body>

<div id="header">
  <p>&nbsp;</p>
  <p><img src="drawable/title.png" width="408" height="75" alt="title"></p>
 
</div>

<div id="content">
  <div id="contentL">
 <?php

	//此判斷為判定觀看此頁有沒有權限
	//說不定是路人或不相關的使用者
	//因此要給予排除
	if($_GET['id'] == 'null' || $_GET['id'] == null)
	{
		echo '<a href="login.php?id='.$_GET['id'].'&pur='.$_GET['pur'].'"><img src="drawable/login_bt.png" width="100" height="50" alt="add"></a> <br> <br>';

	}
	//轉移農夫確認訂單
	if($_GET['pur']==1)
	{
		echo '<a href="logout.php"><img src="drawable/logout_bt.png" width="100" height="50" alt="add"></a>  <br><br>';
		echo '<a href="personal.php?id='.$_GET['id'].'&pur='.$_GET['pur'].'"><img src="drawable/per_bt.png" width="100" height="50" alt="add"></a> <br><br>';
		echo '<a href="check.php?id='.$_GET['id'].'&pur='.$_GET['pur'].'"><img src="drawable/b_bt.png" width="100" height="50" alt="add"></a> <br><br>';
	}
	//轉移民眾顯示訂單
	if($_GET['pur']==2)
	{
		echo '<a href="logout.php"><img src="drawable/logout_bt.png" width="100" height="50" alt="add"></a>  <br><br>';
		echo '<a href="personal.php?id='.$_GET['id'].'&pur='.$_GET['pur'].'"><img src="drawable/per_bt.png" width="100" height="50" alt="add"></a> <br><br>';
		echo '<a href="search.php?id='.$_GET['id'].'&pur='.$_GET['pur'].'"><img src="drawable/b_bt.png" width="100" height="50" alt="add"></a> <br><br>';
	}
	?>

 
   </div>
  <div id="cover">
  <div id="add" align="right">
  <a href="javascript:add();"><img src="drawable/add_bt.png" width="100" height="43" alt="add"></a>

 
</div>
  <div id="main" style="height: 400px; overflow-y: scroll; margin: 10px;">
   <table align="center" rules="rows">
   <tr>
    <th width="150" height="40" scope="col">
<p style="font-size:15px">蔬果名稱</p>
    <th width="420" scope="col">
	<p style="font-size:15px">蔬果資訊</p>
    </th>
  </tr>
   <?php
        while($crop = $sql_query_limit->fetch(PDO::FETCH_OBJ)) { 
			if($crop->amount>0){
	?>
  <tr>
    <th width="150" height="100" scope="col">
	<p style="font-size:15px"><?php echo $crop->vf_name; ?></p></th>
    <th width="420" scope="col">
	<p style="font-size:13px">數量:<?php echo $crop->amount; ?>單價:<?php echo $crop->price; ?>　登記日期:<?php echo $crop->crop_date; ?>收成日期:<?php $date=$crop->carry_date;if($date == '0000-00-00' ){echo "未收成";}else{echo $date;} ?> 預估收成日期:<?php $date=$crop->willcarry_date;if($date == '0000-00-00' ){echo "已收成";}else{echo $date;} ?></p>
     <p style="font-size:10px">農夫:<?php echo $crop->name; ?>　　
		<a href="javascript:edit(<?php echo $crop->f_account; ?>,<?php   $date=$crop->carry_date;if($date == '0000-00-00' ){echo "1";}else{echo "0";}?>,<?php echo $crop->crop_id; ?>);">編輯</a>
		 <a href="javascript:buy_item(<?php echo $crop->crop_id; ?>);">下單</a>
		</p>
    </th>
  </tr>

	<?php }
	} ?>
  </table>
  </div>
  <table border="0" align="center">
<tr><td>  
          
<?php
// 顯示的頁數範圍
$range = 2;
 
// 若果正在顯示第一頁，無需顯示「前一頁」連結
if ($num_pages > 1) {
  // 使用 << 連結回到第一頁
  echo " <a href={$_SERVER['PHP_SELF']}?page=1&id=".$_GET['id']."&pur=".$_GET['pur']."><<</a> ";
  // 前一頁的頁數
  $prevpage = $num_pages - 1;
  // 使用 < 連結回到前一頁
  echo " <a href={$_SERVER['PHP_SELF']}?page=".$prevpage."&id=".$_GET['id']."&pur=".$_GET['pur']."><</a> ";
} // end if
 
// 顯示當前分頁鄰近的分頁頁數
for ($x = (($num_pages - $range) - 1); $x < (($num_pages + $range) + 1); $x++) {
  // 如果這是一個正確的頁數...
  if (($x > 0) && ($x <= $total_pages)) {
    // 如果這一頁等於當前頁數...
    if ($x == $num_pages) {
      // 不使用連結, 但用高亮度顯示
      echo " [<b>".$x."</b>] ";
      // 如果這一頁不是當前頁數...
    } else {
      // 顯示連結
      //改成目前要使用的網址
      echo " <a href=index.php?page=".$x."&id=".$_GET['id']."&pur=".$_GET['pur'].">".$x."</a> ";
    } // end else
  } // end if
} // end for
 
// 如果不是最後一頁, 顯示跳往下一頁及最後一頁的連結
if ($num_pages != $total_pages) {
  // 下一頁的頁數
  $nextpage = $num_pages + 1;
  // 顯示跳往下一頁的連結
  echo " <a href={$_SERVER['PHP_SELF']}?page=".$nextpage."&id=".$_GET['id']."&pur=".$_GET['pur'].">></a> ";
  // 顯示跳往最後一頁的連結
  echo " <a href={$_SERVER['PHP_SELF']}?page=".$total_pages."&id=".$_GET['id']."&pur=".$_GET['pur'].">>></a> ";
} // end if
?>
</td></tr>
</table> 
  </div>
 
</div>

<div id="last"> 
   <p>連絡電話：(05)534-2601<br>
    64002 雲林縣斗六市大學路3段123號<br>
  一鍵解決蔬果供應問題</p>
</div>
</body>
</html>
