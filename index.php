<?php

// кэш выкл
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

$user 	  = 'genius-aut_root';
$password = 'rOOt404RooT';
$db   	  = 'genius-aut_risksport';
$host 	  = 'genius-aut.mysql';
$port 	  = 3306;


function DBgetReserves ($date,$select,$table){ // вытаскиваем резервы из базы по дате $date

	global $user, $password, $db, $host, $port;
//date('Ymd', strtotime($date))
	$link 	  = mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
	$sql  = 'SELECT '.$select.' FROM '.$table.' WHERE `day`="'.$date.'"'; // выделить courttime из таблицы reserves где day = $date
	$result   = mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link)); // отправляем запрос по корту и времени
	
	if($result) // если есть ответ
	{
		$reserveArr = mysqli_fetch_row($result); // вытаскиваем строку из ответа
	    mysqli_free_result($result); // освобождаем ресурсы
	}

	mysqli_close($link); // закрываем соединение

	if ($reserveArr){
		$output = explode(',', $reserveArr[0]); // пишем в новый массив полученную строку, удаляя разделители
	}
	else {
		$output[0] = 0;
	}
	
	return array($output);
}

function DBgetOrders ($orderid,$select,$table){ // вытаскиваем резервы из базы по дате $date

	global $user, $password, $db, $host, $port;

	$link 	  = mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
	$sql  = 'SELECT '.$select.' FROM '.$table.' WHERE `orderid`="'.$orderid.'"'; // выделить courttime из таблицы orders где orderid = $orderid
	$result   = mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link)); // отправляем запрос по корту и времени
	
	if($result) // если есть ответ
	{
		$reserveArr = mysqli_fetch_row($result); // вытаскиваем строку из ответа
	    mysqli_free_result($result); // освобождаем ресурсы
	}

	mysqli_close($link); // закрываем соединение

	if ($reserveArr){
		$output = explode(',', $reserveArr[0]); // пишем в новый массив полученную строку, удаляя разделители
	}
	else {
		$output[0] = 0;
	}
	
	return array($output);
}

function Platron($date,$order){

	$seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ'.'0123456789'); // преобразуем строки в массив $seed
	shuffle($seed); // перемешиваем так, от нечего делать :-)
	$code = '';
	foreach (array_rand($seed, 5) as $k) $code .= $seed[$k];

  	$pg_script       = 'payment.php';
	$pg_amount       = '1';
	$pg_currency     = 'RUB';
	$pg_description  = 'subscribe on '.$date;
	$pg_language     = 'ru';
	$pg_merchant_id  = '12830';
	$pg_order_id	 = $date.'_'.$code;
	$pg_salt         = bin2hex(random_bytes(10));
	$secretkey       = 'nyluzahypegulyho';
	$pg_sig          = md5("$pg_script;$pg_amount;$pg_currency;$pg_description;$pg_language;$pg_merchant_id;$pg_order_id;$pg_salt;$secretkey");

	// тут производим запись $pg_order_id в БД в таблицу orders, чтобы после успешной оплаты записать в таблицу reserves и сменить поле с pay на payed
	//date('ymd', strtotime($date)); <- эту дату запишем вместе с заказом в БД, таблица temp
	///////////////////////////////////////////
	// connect to DB
	///////////////////////////////////////////
	global $user, $password, $db, $host, $port;
	$link 	= mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
	$sql 	= "INSERT INTO orders (orderid, courttime) VALUES ('".$pg_order_id."', '".$order."')";

	if (mysqli_query($link, $sql)) {
		echo '
		<html>
			<body onload="document.forms[0].submit()">
				<form method="POST" action="https://www.platron.ru/'.$pg_script.'">
					<input type="hidden" name="pg_amount" value="'.$pg_amount.'" />
					<input type="hidden" name="pg_currency" value="'.$pg_currency.'" />
					<input type="hidden" name="pg_description" value="'.$pg_description.'" />
					<input type="hidden" name="pg_language" value="'.$pg_language.'" />
					<input type="hidden" name="pg_merchant_id" value="'.$pg_merchant_id.'" />
					<input type="hidden" name="pg_order_id" value="'.$pg_order_id.'" />
					<input type="hidden" name="pg_salt" value="'.$pg_salt.'" />
					<input type="hidden" name="pg_sig" value="'.$pg_sig.'" />
				</form>
			</body>
		</html>';
	}
	else {
		echo "Error: " . $sql . "<br>" . mysqli_error($link);
  	}
  	mysqli_close($link);

}

function stepBack($date){
	
	echo '
	<html>
		<body onload="document.forms[0].submit()">
			<form method="POST">
				<input type="hidden" name="date" value="'.$date.'" />
			</form>
		</body>
	</html>';

}

function checkHack($date, $order,$step){

	list($reserved)=DBgetReserves($date, "`courttime`", "`reserves`"); // присваиваем массиву корт-время для последующей проверки
	$order = explode(',', $order); // переделываем в нормальный массив
	//var_dump ($reserved);
	$checkArrays = array_intersect($order, $reserved); // сравниваем два массива: заказы и зарезервированные. Если произошла подмена, то

	if (!empty($checkArrays)) { // если есть совпадения - произошла подмена
	
		stepBack($date); // POST'им на ту же дату :-)

	}

	else{
		$order = implode(",", $order);
		if ($step=="init"){
			Platron($date,$order);
		}
		//else
		//if ($step=="result"){
		//	echo "result";
		//}
		
	}

}


function showTable($date){	//	таблица с резервом и форма для резервирования	
	echo '
	<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Cache-Control" content="no-cache">
		<link rel="stylesheet" href="style.css">';
		include "dis.htm";
	echo '
		<script type="text/javascript" src="getCell.js"></script>
		<link href="cal.css" rel="stylesheet">
		<title>Резервирование теннисного корта</title>
	</head>
	<body class="default">
	';
	//echo date("H:i:s"); // ВЫСТАВИ ВРЕМЯ НА СЕРВЕРЕ ПРАВИЛЬНО!!!
	//date_default_timezone_set(’Europe/Moscow’);

	$clubOpen = 9; // корты открываются в 9:00
	$clubClose = 22; //	корты закрываются в 22:00
	$offset1 = 100; // смещение для номера корта. значения времени начинающиеся с 1 относятся к первому корту: 109, 110, 111 ...
	$offset2 = $offset1 + 100; // аналогично предыдущему, но для второго корта

	list($reserved)=DBgetReserves($date, "`courttime`", "`reserves`"); // присваиваем массиву корт/время для отображения зарезервированного времени

	echo '
		<script type="text/javascript">
			var userOrder = [];
		</script>
		<div class="tableRes radius">
		<div class="tableResHeader">
			<form name="dateFinder" id="dateFinder" method="POST">
				Выберите дату: <input type="text" name="date" id="inpdate" class="date hand radius inp" value="'.$date.'" autocomplete="off" readonly onclick="showCal();" onfocus="showCal();" onKeyPress="javascript: return false;" onKeyDown="javascript: return false;" onPaste="javascript: return false;"  />
			</form>
		</div>
			<div class="tableResCell left">-</div>';

	for ($time=$clubOpen; $time<$clubClose; $time++){ // показываем в таблице почасовой доступный диапазон - 9:00-21:00
		if ($time != ($clubClose-1)){ // если время НЕ равно времени закрытия -1 час, то рисуем обычные ячейки
			echo '
			<div class="tableResCell left">'.$time.':00</div>';
		}
		else { // иначе рисуем закрывающую ячейку
			echo '
			<div class="tableResCellEnd left">'.$time.':00</div>';
		}
	}	
	echo '	<div class="tableResCell left">1</div>';
	for ($time=$clubOpen; $time<$clubClose; $time++){	// показываем ячейки первого корта
		$id = $offset1 + $time;

		if ($time != ($clubClose-1)){ // если время НЕ равно времени закрытия -1 час, то рисуем обычные ячейки
			echo '		<div class="tableResRow left canselect" id="'.$id.'" onclick="res(id)">&nbsp;</div>
	';
		}
		else { // иначе рисуем закрывающую ячейку
			echo '		<div class="tableResRowEnd left canselect" id="'.$id.'" onclick="res(id)">&nbsp;</div>';
		}
	}
	echo '
			<br />
			<div class="tableResCell left">2</div>';

	for ($time=$clubOpen; $time<$clubClose; $time++){	//	показываем ячейки второго корта
		$id = $offset2 + $time;

		if ($time != ($clubClose-1)){ // если время НЕ равно времени закрытия -1 час, то рисуем обычные ячейки
			echo '
			<div class="tableResRow left canselect" id="'.$id.'" onclick="res(id)">&nbsp;</div>';
		}
		else { // иначе рисуем закрывающую ячейку
			echo '
			<div class="tableResRowEnd left canselect" id="'.$id.'" onclick="res(id)">&nbsp;</div>';
		}
	}
	$onclickfunc = "document.getElementById('toPl').submit();";
	echo '	
			<div class="tableResFooter left">
				<div class="left box bgreen">&nbsp;</div>
				<div class="left boxinfo">- свободно для резерва</div>
				<div class="left box byellow">&nbsp;</div>
				<div class="left boxinfo">- в процессе резервирования</div>
				<div class="left box bred">&nbsp;</div>
				<div class="left boxinfo">- зарезервировано</div>
				<div class="hand resBtn right radius" id="magicBtn" onclick="'.$onclickfunc.'">Зарезервировать</div>
			</div>
		</div>
		<form method="POST" id="toPl">
			<input type="hidden" name="date"  value="'.$date.'" />
			<input type="hidden" name="order" value="" id="order" />
		</form>';		

	if ($reserved[0] != 0){
		echo '	<script>';
		for($i = 0; $i < count($reserved); $i++){
			if (($reserved[$i] == ($clubClose-1 + $offset1)) || ($reserved[$i] == ($clubClose-1 + $offset2))){
				echo '
			document.getElementById('.$reserved[$i].').className="tableResRowEnd left reserved";';
			}
			else
			{
				echo '
			document.getElementById('.$reserved[$i].').className="tableResRow left reserved";';	
			}
		}
		echo '
		</script>';	
	}
	include "cal.htm";
	echo '
	</body>
</html>
';

}

if(isset($_POST['date'])){	//	если есть параметр date 
	if (isset($_POST['order'])){
		checkHack($_POST['date'],$_POST['order'],"init"); // передаем данные на проверку: дата и заказ
	}
	else {

		$date = $_POST['date']; 	//	присваиваем переменной $date значение параметра date 
		if (strtotime($date) < strtotime(date('d-m-Y'))){ // если выбранная пользователем дата раньше текущей
			$date = date('d-m-Y');
			showTable($date);
		}

		else if (strtotime($date) > strtotime(date('d-m-Y', strtotime("+30 days")))){ // если выбранная дата слишком поздняя: текущая + 30 дней
			echo '<script>alert("Резерв можно делать от текущей даты + 30 дней. Пожалуйста, выберите другую дату.");</script>';
			$date = date('d-m-Y');
			showTable($date);
		}

		else {
			showTable($date);		// иначе если выбранная дата равна или позже текущей - показываем таблицу для резерва
		}
	}

}

else{						// если же параметр date отсутствует
	if((isset($_POST['pg_result'])) && (isset($_POST['pg_order_id']))){
		// result - очередная проверка перед финальным записыванием резерва.
		$pg_salt = mt_rand();
		$pg_script = '';
		$pg_order_id = $_POST['pg_order_id'];
		$secretkey = 'nyluzahypegulyho';

		$date = explode("_", $pg_order_id);
		//echo $date[0]."</br>";
		list($reserved)=DBgetReserves($date[0], "`courttime`", "`reserves`"); // присваиваем массиву корт-время для последующей проверки
		list($order)=DBgetOrders($pg_order_id,"`courttime`","`orders`");
		$checkArrays = array_intersect($order, $reserved); // сравниваем два массива: заказы и зарезервированные. Если произошла подмена, то
		
		global $user, $password, $db, $host, $port;
		
		if (!empty($checkArrays)) { // если есть совпадения - произошла ошибка? кто-то уже зарезервировал выбранное
			// удалить из бд запись о заказе??? или пусть висит?
			$link 	= mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
			$sql	= "DELETE FROM `orders` WHERE `orderid` = '".$pg_order_id."'";
			mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
			mysqli_close($link);
			$pg_status = 'rejected'; // режектим платеж
			$pg_description = 'Резерв '.$pg_order_id.' не выполнен';
		}
		else{
			if (!isset($_POST['pg_failure_code'])){
				if ($reserved[0] == 0){ // если резервов по данной дате нет вообще
					// создаем новую запись в талице reserves с перечнем корт-время из заказа order в столбец courttime
					$link 	= mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
					$order = implode(",",$order);
					$sql	= "INSERT INTO `reserves` (`day`, `courttime`) VALUES ('".$date[0]."', '".$order."')";
					mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
					mysqli_close($link);
				}
				else{ // есть резервы - обновляем по данной дате данные в БД
					$updrecord = array_merge($reserved, $order);
					$updrecord = implode(",", $updrecord);
					$link 	= mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
					$sql	= "UPDATE `reserves` SET `courttime` = '".$updrecord."' WHERE `day` = '".$date[0]."'";
					mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
					mysqli_close($link);
				}
				$pg_description = 'Резерв '.$pg_order_id.' выполнен';
			}
			else{
				// удалить из бд запись о заказе??? или пусть висит?
				$link 	= mysqli_connect($host, $user, $password, $db) or die("Ошибка " . mysqli_error($link)); // подключение к БД
				$sql	= "DELETE FROM `orders` WHERE `orderid` = '".$pg_order_id."'";
				mysqli_query($link, $sql) or die("Ошибка " . mysqli_error($link));
				mysqli_close($link);
				$pg_description = 'Ошибка на стороне пс';
			}
			$pg_status = 'ok'; // соглашаемся что все ок (так же в случае ошибки на стороне пс)
		}
		$pg_sig = md5("$pg_script;$pg_description;$pg_salt;$pg_status;$secretkey");
		//header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>
<response>
 <pg_salt>'.$pg_salt.'</pg_salt>
 <pg_status>'.$pg_status.'</pg_status>
 <pg_description>'.$pg_description.'</pg_description>
 <pg_sig>'.$pg_sig.'</pg_sig>
</response>';
	}
	else
	if((!isset($_POST['pg_result'])) && (isset($_POST['pg_order_id']))) {
		// success или fail
		echo '<html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
		if (isset($_POST['pg_failure_code'])){
			// fail - если платеж не прошел
			echo '<br /><font color="red">Ошибка на стороне пс!</font>';
		}
		else{
			// success - платеж прошел успешно
			echo '<br /><font color="green">успешно!</font>';
			echo '<br />номер Вашего заказа '.$_POST['pg_order_id'];
		}
		$webhost = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://'.$_SERVER["HTTP_HOST"];
		echo '<br /><a href="'.$webhost.'/_risk">Вернуться на главную</a>';
		echo '</body></html>';
	}
	else
	if((!isset($_POST['pg_result'])) && (!isset($_POST['pg_order_id']))) {
		// не result и не success/fail
		$date = date('d-m-Y');	//	переменной $date присваиваем значение текущей даты
		showTable($date);		//	показываем таблицу для резерва с текущей датой	
	}

}

?>