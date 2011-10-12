<?php
#UTF-8
/**
 * @category ToolsAndUtilities
 * @package PHPSMPClass
 * @version VERSION_
 * @author Klyunnikov Maksim <klyunnikov.maksim@gmail.com>
 * @copyright 2009-2010 Klyunnikov Maksim
 * @link http://localhost
 * @donate webmoney: R133552307986, Z172480225025
 */

define('PROJECT_', 'PHPSMPClass');
define('VERSION_', '0.1.3.1');
define('DATE_', '2011-03-24 11:20:00');
define('MAXX', 'author: Klyunnikov Maksim <klyunnikov.maksim@gmail.com>, icq: 335521857');
define('HOST', '127.0.0.1');
define('PORT', '10001');
define('PASSWD', '100100');
define('SOCKET_TIME_OUT', 3);


class PHPSMPClass {
		
	var $dev = array(
		0 => 'NONE',
		1 => 'MAL',
		2 => 'MPA',
		3 => 'MPB',
		4 => 'ISDN',
		5 => 'C412'
	);

	var $net_mes = array(
		0x04 => 'HELLO',
		0x1D => 'TUNE_SET',
		0x1E => 'TUNE_SETOK',
		0x1F => 'TUNE_SETERROR',
		0x20 => 'TUNE_GET',
		0x21 => 'TUNE_GETOK',
		0x22 => 'TUNE_GETERROR'
	);

	var $rus_key = array(
		'scomm'                => 'Работа через модуль',
		'scconn'               => 'Номер соединения (n+1 com-port, n-9 ethernet)',
		'module'               => 'Модуль',
		'module_name'          => 'Имя модуля из файла конфигурации',
		
		'USE'                  => 'Включен',
		'SLOT'                 => 'Слот',
		'HOLE'                 => 'Порт',
		'AON'                  => 'Номер АОН',
		'CATAON'               => 'Категория АОН',
		'REDIRECT'             => 'Номер переадресации',
		'ALARM'                => 'Время пробуждения',
		'PINCODE'              => 'PIN-код',
		'PRIVLEVEL'            => 'Уровень привилегий',
		'TEI'                  => 'TEI',
		'CMN_TONE'             => 'Тональный набор',
		'CMN_OUTCOME'          => 'Исходящая связь',
		'CMN_INCOME'           => 'Входящая связь',
		'CMN_EXTERNAL'         => 'Внешние номера',
		'CMN_PAYSERVICE'       => 'Платные службы',
		'CMN_8xxx'             => 'Междугородняя связь',
		'CMN_10xxx'            => 'Международная связь',
		'CMN_AON'              => 'Выдача АОНа',
		'CMN_82xxx'            => 'Выход на 82 ',
		'ENB_FLASH'            => 'Разрешение Flash',
		'ENB_REDIRECT'         => 'Разрешение переадресации',
		'ENB_ALARM'            => 'Разрешение будильника',
		'ENB_PINCODE'          => 'Разрешение пинкода',
		'ENB_AUTODIAL'         => 'Автодозвон',
		'ENB_INTERCEPT'        => 'Вмешательство',
		'ENB_NOTIFICATION'     => 'Уведомление',
		'ENB_EVILCALL'         => 'Злонамеряный вызов',
		'ENB_CALLERID'         => 'Выдача CallerID ',
		'DVO_REDIRECT_ALWAYS'  => 'Переадресация всегда',
		'DVO_REDIRECT_BUSY'    => 'Переадресация по занятости',
		'DVO_REDIRECT_SILENCE' => 'Переадресация по неответу',
		'DVO_REDIRECT_HOTCALL' => 'Горячий вызов',
		'DVO_ALARM'            => 'Состояние будильника',
		'DVO_NOTIFICATION'     => 'Уведомление',
		'DVO_NODISTURB'        => 'Не беспокоить',
		'TYPE_SUBSCRIBER'      => '(0=ANALOG, 1=?)',
		'DVO_PINCODE'          => 'PIN-код на м/г и м/н',
		'DVO_CLIR'             => 'Сокрытие номера',
	);

	/**
	 * Массив телефонов с модулями, портами и слотами
	 *
	 */
	var $arr_tunes = array();
	
	/**
	 * Массив модулей с именами
	 *
	 */
	var $arr_module_name = array();

	/**
	 * Флаг "консоли"
	 *
	 */
	var $console;

	/**
	 *
	 */
	var $socket;

	/**
	 * Статус бинарного режима
	 *
	 */
	var $status_binmode = false;

	/**
	 * Перенос строки
	 *
	 */
	var $line_br = "\n";
	
	/**
	 * Constructor
	 *
	 */
	function PHPSMPClass() {
		$this->CheckBrowser();
		/*
		if (isset($argv)) {
			for ($i=1;$i < $argc;$i++) {
				parse_str($argv[$i],$tmp);
				$_GET = array_merge($_GET, $tmp);
			}
		}
		*/
	}

	/**
	 * Подключение
	 *
	 * @param string $host
	 * @param string $port
	 */
	function Connect($host = HOST, $port = PORT) {
		#Сброс переменой
		$this->status_binmode = false;

		if (trim($host) == '') {
			$this->_Error("Connect.address = NULL");
			return false;
		}
		if (trim($port) == '') {
			$this->_Error("Connect.port = NULL");
			return false;
		}

		$this->F_e("fsockopen");

		$this->Log("Connect(\$host='$host', \$port=$port)...");

		if (!($this->socket = fsockopen($host, $port, $errno, $errstr, 3))) {
			$this->_Error("Socket - $errstr. Error[$errno]");
			return false;
		}
		else {
			$this->Log("Socket - OK.");
		}
		return true;
	}

	/**
	 * Закрытие
	 */
	function Close() {
		$this->F_e("fclose");
		if (fclose($this->socket)) {
			$this->Log('Socket_close - OK');
		}
		else {
			$this->_Error('Socket_close - ERROR');
			return false;
		}
	}

	/**
	 * Переключение в бинарный режим
	 *
	 * @param string $passwd
	 */
	function Binmode($passwd = PASSWD, $timeout = SOCKET_TIME_OUT) {

		#Проверяем включен бинарный режим
		if ($this->status_binmode) {
			$this->_Error("BINARYMODE-ON!");
			return false;
		}

		if (trim($passwd) == '') {
			$this->_Error("Binmode.passwd == NULL");
			return false;
		}

		if (trim($timeout) == '') {
			$this->_Error("Binmode.timeout == NULL");
			return false;
		}

		$this->F_e("fgets");
		$this->Log("Binmode(\$passwd=$passwd, \$timeout=$timeout)...");
		$ready_ok = false;
		$r_sock = '';

		$this->F_e("stream_set_timeout");

		$this->Log("Wait: R E A D Y...");

		stream_set_timeout($this->socket, $timeout);
		
		$info = stream_get_meta_data($this->socket);

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock .= fgets($this->socket, 2);
			if (preg_match("(R E A D Y)", $r_sock)) {
				$this->Log("R E A D Y - OK");
				$ready_ok = true;
				break;
			}
			elseif (preg_match("(I_AM_SPIDER)", $r_sock)) {
				$this->_Error("R E A D Y - ERROR: it's no SComm. I_AM_SPIDER");
				return false;
			}
			elseif (strlen($r_sock) > 256) { //если прийдет больше, чем положенно
				$this->_Error("R E A D Y - ERROR: $r_sock");
				return false;
			}
			$info = stream_get_meta_data($this->socket);
			#print_r ($info); #DEBUG
		}

		#Выходим, если во время таймаута не пришел R E A D Y
		if (!($ready_ok)) {
			$this->_Error("R E A D Y - ERROR TIMEOUT: $r_sock");
			return false;
		}

		$w_sock = "BINARYMODE-" . $passwd;
		$this->Log("$w_sock");
		fwrite($this->socket, $w_sock . "\n\r");
		$this->Log("Wait: $w_sock...");

		$r_sock = '';

		$info = stream_get_meta_data($this->socket);

		while (!$info['timed_out']) {
			$r_sock .= fgets($this->socket, 2);
			if (preg_match("(BINARYMODE-OK[\s]{2})", $r_sock)) {
				$this->Log('BINARYMODE-OK');
				$this->status_binmode = true;
				break;
			}
			elseif (preg_match("(BINARYMODE-ER[\s]{2})", $r_sock)) {
				$this->_Error('BINARYMODE-ER');
				return false;
			}
			elseif (strlen($r_sock) > 256) { //если прийдет больше, чем положенно
				$this->_Error("BINARYMODE-ERROR: $r_sock");
				return false;
			}
			$info = stream_get_meta_data($this->socket);
			#print_r ($info); #DEBUG
		}

		if (!$this->status_binmode) {
			$this->_Error("BINARYMODE-TIMEOUT: $r_sock");
			return false;
		}
		return true;
	}

	/*
	 * Пробую работать с ats ELCOM
	 *
	 * @param $message, $timeout
	 */
	function ELCOM($message, $timeout = SOCKET_TIME_OUT) {
		#Проверяем включен бинарный режим
		if ($this->status_binmode) {
			$this->_Error("BINARYMODE-ON! Delete Binmode(<pass>)");
			return false;
		}

		if (trim($message) == '') {
			$this->_Error("ELCOM.message == NULL");
			return false;
		}

		if (trim($timeout) == '') {
			$this->_Error("ELCOM.timeout == NULL");
			return false;
		}

		$this->Log("ELCOM wait \$timeout = $timeout ...");
		$this->F_e("stream_set_timeout");

		/*
		# Запрос номера 483214
		T 192.168.0.7:40560 -> 192.168.0.140:5005 [AP]
		  01 6e 06 00 ff ff 09 48    32 14 0a                   .n.....H2..
		# Ответ1 (останется разобраться с пакетом)
		T 192.168.0.140:5005 -> 192.168.0.7:40560 [AP]
		  01 0c 04 00 0b 36 00 01    53                         .....6..S
		## Ответ2 ((останется разобраться с пакетом))
		T 192.168.0.140:5005 -> 192.168.0.7:40560 [AP]
		  01 0c 13 00 0b 36 00 00    99 48 32 14 48 32 14 01    .....6...H2.H2..
		  00 00 42 <d8> 15 00 63 a9
		*/

		/*
		# Запись1 карточки номера 483214
		T(6) 192.168.0.7:43311 -> 192.168.0.140:5005 [AP]
		  01 6f 04 00 0b 36 00 01    b6                         .o...6...
		## Запись2
		T(6) 192.168.0.7:43311 -> 192.168.0.140:5005 [AP]
		  01 6f 13 00 0b 36 00 00    99 48 32 14 48 32 14 01    .o...6...H2.H2..
		  00 00 42 c8 15 00 63 fc                               ..B...c.
		# Ответ
		T(6) 192.168.0.140:5005 -> 192.168.0.7:43311 [AP]
		  01 0e 02 00 6f 01 81                                  ....o..
		#
		*/

		/*
		# Запрос несуществующего номера 555555
		T 192.168.0.7:55836 -> 192.168.0.140:5005 [AP]
		  01 6e 06 00 ff ff 09 55    55 55 7b                   .n.....UUU{
		## Ответ1 (останется разобраться с пакетом)
		T 192.168.0.140:5005 -> 192.168.0.7:55836 [AP]
		  01 1f 07 00 02 09 55 55    55 ff ff 2f                ......UUU../
		*/

		$r_sock = '';

		$w_sock = "$message";

		fwrite($this->socket, $w_sock);

		stream_set_timeout($this->socket, $timeout); //таймаут
		$info = stream_get_meta_data($this->socket);

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock = fgets($this->socket, 4096);
			$info = stream_get_meta_data($this->socket);
			print_r($r_sock);
			//print_r($info);
		}
		$this->Log("Done.");
	}


	/*
	 * Для работы с TUNE
	 *
	 * @param $message, $timeout
	 */
	function TUNE_Lite($message, $timeout = SOCKET_TIME_OUT) {
		#Проверяем включен бинарный режим
		if ($this->status_binmode) {
			$this->_Error("BINARYMODE-ON! Delete Binmode(<pass>)");
			return false;
		}

		if (trim($message) == '') {
			$this->_Error("TUNE.message == NULL");
			return false;
		}

		if (trim($timeout) == '') {
			$this->_Error("TUNE.timeout == NULL");
			return false;
		}

		$this->F_e("stream_set_timeout");
		
		$r_sock = '';
		
		$this->Log("TUNE_Lite(\$message='$message', \$timeout=$timeout)...");

		$w_sock = "$message\r";
		fwrite($this->socket, $w_sock);

		stream_set_timeout($this->socket, $timeout); //таймаут
		$info = stream_get_meta_data($this->socket);

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock .= fgets($this->socket, 4096);
			$info = stream_get_meta_data($this->socket);
			#print_r ($info); #DEBUG
		}
		$this->Log("Done.");
		return $r_sock;
	}

	/*
	 * Для работы с TUNE
	 *
	 * @param $message, $timeout
	 */
	function TUNE($message, $timeout = SOCKET_TIME_OUT) {
		#Проверяем включен бинарный режим
		if ($this->status_binmode) {
			$this->_Error("BINARYMODE-ON! Delete Binmode(<pass>)");
			return false;
		}

		if (trim($message) == '') {
			$this->_Error("TUNE.message == NULL");
			return false;
		}

		if (trim($timeout) == '') {
			$this->_Error("TUNE.timeout == NULL");
			return false;
		}

		$this->F_e("stream_set_timeout");
		
		$r_sock = '';
		
		$this->Log("TUNE(\$message='$message', \$timeout=$timeout)...");

		$w_sock = "$message\r";
		fwrite($this->socket, $w_sock);

		stream_set_timeout($this->socket, $timeout); //таймаут
		$info = stream_get_meta_data($this->socket);

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock .= fgets($this->socket, 4096);
			if (preg_match("(gettune ok)", $r_sock)) {
				$this->Log("TUNE - gettune ok");
				break;
			}
			elseif (preg_match("(settune ok)", $r_sock)) {
				$this->Log("TUNE - settune ok");
				break;
			}
			/* разобраться с переносами
			elseif (preg_match("(\r\n\r\n)", $r_sock)) {
				$this->Log("nn");
				break;
			}
			*/
			elseif (preg_match("(I_AM_SPIDER)", $r_sock)) {
				$this->_Error("ERROR: it's no TUNE. I_AM_SPIDER");
				$r_sock_value['error'] = "it's no TUNE. I_AM_SPIDER";
				break;
			}
			elseif (strlen($r_sock) > 256 && !(preg_match("(CMD)", $r_sock))) { //если прийдет больше, чем положенно
				$this->_Error("ERROR - long message: $r_sock");
				$r_sock_value['error'] = 'long message';
				break;
			}
			elseif (preg_match("(error read tune)", $r_sock)) {
				$this->_Error("TUNE - error read tune");
				$r_sock_value['error'] = 'error read tune';
				break;
			}
			elseif (preg_match("(gettune need number)", $r_sock)) {
				$this->_Error("TUNE - gettune need number");
				$r_sock_value['error'] = 'gettune need number';
				break;
			}
			elseif (preg_match("(unknown command)", $r_sock)) {
				$this->_Error("TUNE - unknown command");
				$r_sock_value['error'] = 'unknown command';
				break;
			}
			$info = stream_get_meta_data($this->socket);
			#print_r ($info); #DEBUG
		}
		$this->Log("Done.");
		$r_sock_value['message'] = $r_sock;
		return $r_sock_value;
	}

	/*
	 * Преобразование строки от TUNE в массив
	 *
	 * @param $message
	 */
	function TUNE_GETTUNE_ARRAY($message) {
		$message_value = array();
		$message_split = explode("\n",$message);
		foreach ($message_split as $i => $value) {
			$message_count = explode('=',$value);
			if (count($message_count)==2) {
				$message_value[$message_count[0]] = $message_count[1];
			}
		}
		//$message_value['_TUNE_MES'] = $message;
		return $message_value;
	}

	/*
	 * Вывести модули в GSCP сети (как в SMPAdmin [F6])
	 *
	 * @param $timeout
	 */
	function F6($timeout = SOCKET_TIME_OUT) {
		#Проверяем включен бинарный режим
		if (!($this->status_binmode)) {
			$this->_Error("BINARYMODE-OFF! Add Binmode(<pass>)");
			return false;
		}

		if (trim($timeout) == '') {
			$this->_Error("F6.timeout == NULL");
			return false;
		}

		$this->Log("F6(\$timeout=$timeout)...");

		#hello
		$w_sock = pack("CC",0x0F,0x00);
		fwrite($this->socket, $w_sock);
		$w_sock = pack("CCCCCCCCCCCCCCC",0xff,0x00,0x00,0x00,0x04,0x00,0x00,0xff,0xff,0xff,0xff,0xff,0xff,0xff,0xff);
		fwrite($this->socket, $w_sock, strlen ($w_sock));

		$r_sock = '';
		$r_sock_temp = '';
		$arr_g_p = array();

		$this->F_e("stream_set_timeout");

		stream_set_timeout($this->socket, $timeout); //таймаут
		$info = stream_get_meta_data($this->socket);

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock_temp = fgets($this->socket, 1024);
			$r_sock .= $r_sock_temp;
			$info = stream_get_meta_data($this->socket);
			//$this->Log("Plese wait, unread_bytes " . $info['unread_bytes']);
			#printf("\rMODULE(S)%'.10s", count(str_split($r_sock, 21))); #DEBUG
			#print_r ($info); #DEBUG
		}
		$arr_r_sock = str_split($r_sock, 21); //разбиваем на по 21 байту

		foreach ($arr_r_sock as $value) {
			if (strlen($value) == 21) {
				$g_p = $this->F6_unpack_21($value);
				$g_p['net_mes'] = $this->net_mes[$g_p['net_mes']];
				
				#Имя модуля
				if (isset($this->arr_module_name[$g_p['module']])) {
					$g_p['module_name'] = $this->arr_module_name[$g_p['module']];
				}
				else {
					$g_p['module_name'] = '';
				}
				
				$arr_g_p[$g_p['module']] = $g_p;
			}
			else {
				$this->_Error($this->Trace_HEX($value,"Pack error:"));
				return false;
			}
		}
		
		$this->Log("Done.");
		return $arr_g_p;
	}


	/*
	 * Вывести строковую информацию с модулями в GSCP сети (как в SMPAdmin [F6])
	 *
	 * @param $timeout
	 */
	function F6_STR($timeout = SOCKET_TIME_OUT) {

		$str = '';
		$arr_f6 = $this->F6($timeout);
		if (count($arr_f6) == 0) {
			return 'Ни один модуль не успел ответить';
			exit;
		}
		
		if (count($this->arr_module_name) != 0) {
			foreach (array_diff_key($this->arr_module_name,$arr_f6) as $key => $value) {
				$str .= "Off-line! [$key] - $value" . $this->line_br;
			}
		}
		
		sort($arr_f6); //сортируем массив
		$format_row = "%3d %03d %-4s %5d %-19s %6s %5d %6d %s" . $this->line_br;
		$format_head = "%-3s %3s %-4s %-5s %-19s %-6s %-5s %-6s %s" . $this->line_br;
		$str .= sprintf($format_head,'id','#','Type','Motor','Config','Status','SComm','SCConn', 'Module_name');
		$id = 1;
		foreach ($arr_f6 as $g_p) {
			$cfgdate_ = mktime($g_p['hour'], $g_p['minute'], $g_p['second'], $g_p['month'],$g_p['day'], $g_p['year']);
			$cfgdate = date("Y-m-d H:i:s", $cfgdate_);
			$str .= sprintf($format_row,
							$id++,
							$g_p['module'],
							$this->dev[$g_p['typemod']],
							$g_p['motor'],
							$cfgdate,
							$g_p['statuscfg'] ? "IGNORE" : "OK",
							$g_p['scomm'],
							$g_p['scconn'],
							$g_p['module_name']
							);
		}
		
		return $str;
	}

	/**
	 * #Байт в бит :)
	 *
	 */
	function Bit_unpack($bit,$str_s) {
		return (int)substr(sprintf("%08b",$bit),$str_s,1);
	}

	/**
	 * #Байт в бит new :)
	 *
	 */
	function Bit_unpack_new($bit,$name_index_array = array(0,1,2,3,4,5,6,7)) {
		$temp_array = array();
		$bit_array = str_split(sprintf("%08b",$bit),1);
		for($x=0;$x<=7;$x++) {
			$temp_array[$name_index_array[$x]] = $bit_array[$x];
		}
		return $temp_array;
	}

	/**
	 * Короткий ответ карточки
	 *
	 */
	function F2_unpack_13($str) {
		#при GETTUNE Сnet_mes => 33 - OK, 34 - ERROR; при SETTUNE Сnet_mes => 30 приходит всегда не зависимо правильный модуль/номер
		//print $this->Trace_HEX($str) , "\n"; #debug
		return unpack("Cc1/Cc2/Cscomm/Cscconn/Cmodule/Cc6/Cnet_mes/Cc8/Cnoname9/Lnumber", $str);
	}

	/**
	 * Нормальный ответ карточки
	 *
	 */
	function F2_unpack_64($str) {
		#при GETTUNE Сnet_mes => 33 - OK, 34 - ERROR; при SETTUNE Сnet_mes => 30 приходит всегда не зависимо правильный модуль/номер
		//print $this->Trace_HEX($str) , "\n"; #debug
		$array_unpack_64 = unpack("Cc1/Cc2/Cscomm/Cscconn/Cmodule/Cc6/Cnet_mes/Cc8/Cnoname9/Lnumber/C_c14/CUSE/C_c15bit/C_c16bit/C_c17bit/C_c18bit/CSLOT/CHOLE/A8AON/CCATAON/A12REDIRECT/A6ALARM/A5PINCODE/CPRIVLEVEL/CTEI/CC56/CC57/CC58/CC59/CC60/CC61/CC62/CC63/CC64", $str);
		#Удаляем строку "\0"....
		foreach ($array_unpack_64 as $key => $value) {
			if (is_string($value)) {
				$pos_0 = strpos($value, "\0");
				if ($pos_0 !== false) {
					$array_unpack_64[$key] = substr($value,0,$pos_0);
				}
			}
		}
		//var_dump($array_unpack_64);
		return $array_unpack_64;
	}

	/**
	 * Ответ HELLO
	 *
	 */
	function F6_unpack_21($str) {
		/*
		SMPAdmin_F6_Return
		01 02 03 04 05 06 07 08 09 10 11 12 13 14 15 16 17 18 19 20 21
		??_??_SC_CC_MO_??_NM_??_??_ty_ot_oM_??_SS:MM:HH_??_DD/mm/YY_cf

		01,02 - длина сообщения!
		SC - Scomm<->Module
		CC - Scomm connect (old = n+1, new = n-8)
		MO - Module
		NM - Код сообщения NET_MES_XXX
		ty - type 00-MAL,03-MRB
		cf - config ingnore (true/false)
		ot oM - Motor
		*/

		//print $this->Trace_HEX($str) , "\n"; #debug
		return unpack("Cс1/Cс2/Cscomm/Cscconn/Cmodule/Cc6/Cnet_mes/Cc8/Cnoname9/Ctypemod/Smotor/Cc13/Csecond/Cminute/Chour/Cс17/Cday/Cmonth/Cyear/Cstatuscfg", $str);
	}

	/**
	 * F2_GET
	 *
	 */
	function F2_GET($number, $module, $timeout = SOCKET_TIME_OUT) {
		#Проверяем включен бинарный режим
		if (!($this->status_binmode)) {
			$arr_g_p[$number]['number'] = $number;
			$arr_g_p[$number]['module'] = $module;
			$arr_g_p[$number]['timeout'] = $timeout;
			$arr_g_p[$number]['error'] = 'BINARYMODE-OFF! Add Binmode(<pass>)';
			$this->_Error("BINARYMODE-OFF! Add Binmode(<pass>)"); //не выходим
			return $arr_g_p;

		}

		if (trim($timeout) == '') {
			$arr_g_p[$number]['number'] = $number;
			$arr_g_p[$number]['module'] = $module;
			$arr_g_p[$number]['timeout'] = $timeout;
			$arr_g_p[$number]['error'] = 'F2_GET.timeout == NULL';
			$this->_Error("F2_GET.timeout == NULL");
			return $arr_g_p;
		}
		
		if (!($module >= 1 && $module <= 128)) {
			if(!($module = $this->Get_number_module($number))) {
				$arr_g_p[$number]['number'] = $number;
				$arr_g_p[$number]['module'] = $module;
				$arr_g_p[$number]['timeout'] = $timeout;
				$arr_g_p[$number]['error'] = 'F2_GET.number == number not found';
				$this->_Error("F2_GET.number == number not found");
			return $arr_g_p;
			}
		}

		$this->Log("F2_GET(\$number=$number, \$module=$module, \$timeout=$timeout)...");

		$num = '1' . $number; //дополняем, что бы не потерять нули в начале номера

		$w_sock = pack("CC",0x0B,0x00);
		fwrite($this->socket, $w_sock);
		$w_sock = pack("CCCCCCCL",$module,0xfd,0xd8,0x00,0x20,0x00,0x12,(int)$num);
		fwrite($this->socket, $w_sock, strlen ($w_sock));

		$r_sock = '';
		$r_sock_temp = '';
		$r_sock_len = 0;
		$arr_g_p = array();

		$this->F_e("stream_set_timeout");

		stream_set_timeout($this->socket, $timeout); //таймаут
		$info = stream_get_meta_data($this->socket);

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock_temp = fgets($this->socket, 2);
			$r_sock .= $r_sock_temp;

			$r_sock_len = strlen($r_sock);
			if ($r_sock_len == 64) {

				$g_p = $this->F2_unpack_64($r_sock);

				$arr_g_p[$number]['GET'] = $g_p;
				
				#Имя модуля
				if (isset($this->arr_module_name[$arr_g_p[$number]['GET']['module']])) {
					$arr_g_p[$number]['GET']['module_name'] = $this->arr_module_name[$arr_g_p[$number]['GET']['module']];
				}

				#Присвавание чекс боксов
				###_15_###
				$arr_g_p[$number]['GET']['c15bit'] = $this->Bit_unpack_new($g_p['_c15bit'],array(
					7 => 'CMN_TONE',
					6 => 'CMN_OUTCOME',
					5 => 'CMN_INCOME',
					4 => 'CMN_EXTERNAL',
					3 => 'CMN_PAYSERVICE',
					2 => 'CMN_8xxx',
					1 => 'CMN_10xxx',
					0 => 'CMN_AON'
				));

				###_16_###
				$arr_g_p[$number]['GET']['c16bit'] = $this->Bit_unpack_new($g_p['_c16bit'],array(
					7 => 'CMN_82xxx',
					6 => '16_6',
					5 => 'ENB_FLASH',
					4 => 'ENB_REDIRECT',
					3 => 'ENB_ALARM',
					2 => 'ENB_INTERCEPT',
					1 => 'ENB_PINCODE',
					0 => 'ENB_AUTODIAL'
				));

				###_17_###
				$arr_g_p[$number]['GET']['c17bit'] = $this->Bit_unpack_new($g_p['_c17bit'],array(
					7 => 'ENB_NOTIFICATION',
					6 => 'ENB_EVILCALL',
					5 => 'ENB_CALLERID',
					4 => '17_4',
					3 => 'DVO_REDIRECT_ALWAYS',
					2 => 'DVO_REDIRECT_BUSY',
					1 => 'DVO_REDIRECT_SILENCE',
					0 => 'DVO_ALARM'
				));
				###_18_###
				$arr_g_p[$number]['GET']['c18bit'] = $this->Bit_unpack_new($g_p['_c18bit'],array(
					7 => 'DVO_REDIRECT_HOTCALL',
					6 => 'DVO_NOTIFICATION',
					5 => 'DVO_NODISTURB',
					4 => 'DVO_PINCODE',
					3 => 'TYPE_SUBSCRIBER',
					2 => 'DVO_CLIR',
					1 => 'DVO_PINCODETWO',
					0 => '18_0'
				));

				//print_r($arr_g_p);

				$this->Log("F2_GET OK!");

				break;
			}
			elseif ($r_sock_len == 13) {
				$g_p = $this->F2_unpack_13($r_sock);
				if ($g_p['c1'] == 11) {
					$arr_g_p[$number]['GET'] = $g_p;
					$arr_g_p[$number]['error'] = 'Number not present in module';
					$this->_Error("F2_GET ERROR! " . $arr_g_p[$number]['error']);
					break;
				}
			}

			$info = stream_get_meta_data($this->socket);
			//print_r ($info); #DEBUG
		}

		###>>>2
		if (count($arr_g_p) == 0) {
			$arr_g_p[$number]['number'] = $number;
			$arr_g_p[$number]['module'] = $module;
			$arr_g_p[$number]['timeout'] = $timeout;
			$arr_g_p[$number]['error'] = 'No reply from the module';
			$this->_Error("F2_GET ERROR! " . $arr_g_p[$number]['error']); //не выходим
		}
		else {
			$arr_g_p[$number]['F2_GET'] = $this->net_mes[$arr_g_p[$number]['GET']['net_mes']];
		}

		$this->Log("Done.");
		return $arr_g_p;
	}

	/**
	 * F2_GET_STR
	 *
	 */
	function F2_GET_STR($number, $module, $timeout = SOCKET_TIME_OUT, $all_paramerts = false) {

		$str = '';

		#Делаем запрос состояния карточки для дальнейшей записи
		$arr_g_p = $this->F2_GET($number,$module,$timeout);
		
		#Делаем проверку на считанную карточку
		if (isset($arr_g_p[$number]['error'])) {
			foreach ($arr_g_p[$number] as $key => $value) {
				$str .= "$key = $value" . $this->line_br;
			}
		}
		else {
			foreach ($arr_g_p[$number]['GET'] as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key_arr => $value_arr) {
						if (isset($this->rus_key[$key_arr])) {
							$str .= "$key_arr = $value_arr //" . $this->rus_key[$key_arr] . $this->line_br;
						}
						else {
							if ($all_paramerts) {
								$str .= "$key_arr = $value_arr" . $this->line_br;
							}
						}
					}
				}
				else {
					if (isset($this->rus_key[$key])) {
						$str .= "$key = $value //" . $this->rus_key[$key] . $this->line_br;
					}
					else {
						if ($all_paramerts) {
							$str .= "$key = $value " . $this->line_br;
						}
					}
				}
			}
		}

		return $str;
	}


	/**
	 * F2_SET
	 *
	 */
	function F2_SET($number, $module, $message_array , $timeout = SOCKET_TIME_OUT) {

		$arr_g_p = array();

		if (!is_array($message_array)) {
			$this->_Error('F2_SET ERROR! $message_array is not array'); //не выходим
			$arr_g_p[$number]['error'] = 'F2_SET ERROR! $message_array is not array';
			return $arr_g_p;
		}

		#Делаем запрос состояния карточки для дальнейшей записи
		$arr_g_p = $this->F2_GET($number,$module,$timeout);

		#Делаем проверку на ошибку считанной карточки
		if (isset($arr_g_p[$number]['error'])) {
			return $arr_g_p;
		}
		
		$module = $arr_g_p[$number]['GET']['module'];
		
		$arr_g_p[$number]['SET'] = $arr_g_p[$number]['GET'];

		$this->Log("F2_SET(\$number=$number, \$module=$module, \$timeout=$timeout)...");

		####################################################################
		foreach ($arr_g_p[$number]['SET'] as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $key_arr => $value_arr) {
					if (isset($message_array[$key_arr])) {
						$arr_g_p[$number]['SET'][$key][$key_arr] = $message_array[$key_arr];
						unset($message_array[$key_arr]);
					}
				}
			}
			else {
				if (isset($message_array[$key])) {
					$arr_g_p[$number]['SET'][$key] = $message_array[$key];
					unset($message_array[$key]);
				}
			}
		}
		$arr_g_p[$number]['KEY_NOT_FOUND'] = $message_array;

		$arr_g_p[$number]['DIFF_GET_SET'] = array_diff_assoc($arr_g_p[$number]['GET'],$arr_g_p[$number]['SET']);
		foreach ($arr_g_p[$number]['SET'] as $key => $value) {
			if (is_array($value)) {
				$arr_g_p[$number]['DIFF_GET_SET'] += array_diff_assoc($arr_g_p[$number]['GET'][$key],$arr_g_p[$number]['SET'][$key]);
			}
		}
		####################################################################

		#Собираем байты из чек-боксов bin--->dec
		$arr_g_p[$number]['SET']['_c15bit'] = base_convert(implode($arr_g_p[$number]['SET']['c15bit'],''),2,10);
		$arr_g_p[$number]['SET']['_c16bit'] = base_convert(implode($arr_g_p[$number]['SET']['c16bit'],''),2,10);
		$arr_g_p[$number]['SET']['_c17bit'] = base_convert(implode($arr_g_p[$number]['SET']['c17bit'],''),2,10);
		$arr_g_p[$number]['SET']['_c18bit'] = base_convert(implode($arr_g_p[$number]['SET']['c18bit'],''),2,10);

		$w_sock = pack("CC",0x3E,0x00);
		fwrite($this->socket, $w_sock);
		#Сборка пакета
		$w_sock  = pack("CCCCCCCL",$module,0xE9,0x90,0x7C,0x1D,0x00,0x91,(int)$arr_g_p[$number]['SET']['number']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['_c14']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['USE']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['_c15bit']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['_c16bit']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['_c17bit']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['_c18bit']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['SLOT']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['HOLE']);
		$w_sock .= pack("a8", $arr_g_p[$number]['SET']['AON']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['CATAON']);
		$w_sock .= pack("a12", $arr_g_p[$number]['SET']['REDIRECT']);
		$w_sock .= pack("a6", $arr_g_p[$number]['SET']['ALARM']);
		$w_sock .= pack("a5", $arr_g_p[$number]['SET']['PINCODE']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['PRIVLEVEL']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['TEI']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C56']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C57']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C58']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C59']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C60']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C61']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C62']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C63']);
		$w_sock .= pack("C", $arr_g_p[$number]['SET']['C64']);

		fwrite($this->socket, $w_sock);
		#$this->Log($this->Trace_HEX($w_sock));

		stream_set_timeout($this->socket, $timeout); //таймаут
		$info = stream_get_meta_data($this->socket);
		$r_sock = '';
		$r_sock_temp = '';
		$r_sock_len = 0;
		$arr_g_p_set = array();

		while(!$info['timed_out']) { #работать по таймауту
			$r_sock_temp = fgets($this->socket, 2);
			$r_sock .= $r_sock_temp;
			$r_sock_len = strlen($r_sock);
			if ($r_sock_len == 13) {

				$arr_g_p_set = $this->F2_unpack_13($r_sock);
				break;
			}
			$info = stream_get_meta_data($this->socket);
			#print_r ($info); #DEBUG
		}

		if (count($arr_g_p_set) == 0) {
			$arr_g_p[$number]['timeout'] = $timeout;
			$arr_g_p[$number]['F2_SET'] = 'error';
			$arr_g_p[$number]['error'] = 'No reply from the module';
			$this->_Error("F2_SET ERROR! " . $arr_g_p[$number]['error']);
		}
		else {
			#Ответ от АТС
			$arr_g_p[$number]['ATS'] = $arr_g_p_set;

			$arr_g_p[$number]['F2_SET'] = $this->net_mes[$arr_g_p_set['net_mes']];

			#проверка, телефон и модуль вернувшийся совпадает с отправленным
			if ($arr_g_p_set['number'] == $arr_g_p[$number]['SET']['number'] && $arr_g_p_set['module'] == $arr_g_p[$number]['SET']['module']) {
				$this->Log("F2_SET OK!");
			}
			else {
				$arr_g_p[$number]['error'] = 'Number not present in module.?.';
				$this->_Error("F2_SET ERROR! " . $arr_g_p[$number]['error']);
			}
		}
		$this->Log("Done.");
		return $arr_g_p;
	}

	/**
	 * Проверка функции
	 *
	 * @param string $functions
	 */
	function F_e($str) {
		if (!function_exists($str)) {
			die("Function is not defined - $str()");
		}
	}


	/**
	 * Cоздание массива из файла конфигурации
	 */
	function Parse_file_SMPCFG($file_cfg = '', $number_conf = 1) {

		if (trim($file_cfg) == '') {
			$this->_Error("Parse_file_SMPCFG.file_cfg == NULL");
			return false;
		}
		
		if (trim($number_conf) == '') {
			$this->_Error("Parse_file_SMPCFG.number_conf == NULL");
			return false;
		}
		
		$this->arr_tunes = array();
		
		$this->Log("Parse_file_SMPCFG(\$file_cfg='$file_cfg')...");
		
		$start_read_file_tunes = microtime(1);
		
		if (!$handle = fopen($file_cfg, 'r')) {
			$this->_Error("Could not open input file: '$file_cfg'");
			return false;
		}
		
		$buffer_out = '';
		
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			if (preg_match("/[^\/]*\/*\s*TIME\s*\=\s*\[([^\]]*)\].*/", $buffer, $b)) {
				$this->Log("Config: " . $b[1]);
			}
			$mas = explode('//', $buffer);
			$buffer_temp = rtrim($mas[0]);
			if ($buffer_temp != '') {
				$buffer_out .= $buffer_temp;
			}
		}
		fclose($handle);
		
		#Парсим CONF
		$arr_conf_temp = explode('CONF ', $buffer_out);
		
		foreach ($arr_conf_temp as $key_arr => $value_arr) {
			if (preg_match("/^\s*\[(\d{1,3})\]\s*{(.*)}\s*/", $value_arr, $pr_mas)) {
				$arr_conf[$pr_mas[1]] = $pr_mas[2];
			}
		}
		if (!isset($arr_conf)) {
			$this->_Error("Parse CONF!");
			return false;
		}
		
		#Парсим MODULE
		foreach ($arr_conf as $key => $value) {
			$arr_module_temp = explode('MODULE ', $value);
			
			foreach ($arr_module_temp as $key_arr => $value_arr) {
				
				if (preg_match("/^\s*\[(\d{1,3})\]\s*{(.*)}\s*/", $value_arr, $pr_mas)) {
					$arr_module[$key][$pr_mas[1]] = $pr_mas[2];
					
					#MODULE_NAME
					if (preg_match("/^.*NAME\s*=\s*\"([^\"]*)\".*/", $value_arr, $module_name_mas)) {
						$this->arr_module_name[$pr_mas[1]] = $module_name_mas[1];
					}
					#
					
				}
			}
		}
		if (!isset($arr_module)) {
			$this->_Error("Parse MODULE!");
			return false;
		}
		
		#Парсим SLOT
		foreach ($arr_module as $key1 => $value1) {
			foreach ($value1 as $key => $value) {
				$arr_slot_temp = explode('SLOT ', $value);
				
				foreach ($arr_slot_temp as $key_arr => $value_arr) {
					if (preg_match("/^\s*\[(\d{1,3}|\d{1,3}-\d{1,3})\]\s*{(.*)}\s*/", $value_arr, $pr_mas)) { 
						$arr_slot[$key1][$key][$pr_mas[1]] = $pr_mas[2];
					}
				}
			}
		}
		if (!isset($arr_slot)) {
			$this->_Error("Parse SLOT!");
			return false;
		}
		
		#Парсим PORT
		foreach ($arr_slot as $key2 => $value2) {
			foreach ($value2 as $key1 => $value1) {
				foreach ($value1 as $key => $value) {
					$arr_slot_temp = explode('PORT ', $value);
					
					foreach ($arr_slot_temp as $key_arr => $value_arr) {
						if (preg_match("/^\s*\[(\d{1,3}|\d{1,3}-\d{1,3})\]\s*{(.*)}\s*/", $value_arr, $pr_mas)) {
							$arr_port[$key2][$key1][$key][$pr_mas[1]] = $pr_mas[2];
						}
					}
				}
			}
		}
		if (!isset($arr_port)) {
			$this->_Error("Parse PORT!");
			return false;
		}
		
		#Парсим NUMBERA
		foreach ($arr_port as $key3 => $value3) {
			foreach ($value3 as $key2 => $value2) {
				foreach ($value2 as $key1 => $value1) {
					foreach ($value1 as $key => $value) {
						if (preg_match("/^.*NUMBERA\s*=\s*\"(\d{1,7}|\d{1,7}[-+])\".*/", $value, $pr_mas)) {
							$arr_numbera[$key3][$key2][$key1][$key] = $pr_mas[1];
						}
					}
				}
			}
		}
		if (!isset($arr_numbera)) {
			$this->_Error("Parse NUMBERA!");
			return false;
		}
		
		
		$deb = 1;
		
		#Создаем массив модулей и телефонов
		foreach ($arr_numbera as $conf => $value4) {
			foreach ($value4 as $module => $value3) {
				foreach ($value3 as $slot => $value2) {
					foreach ($value2 as $port => $number) {
						
						//print "$conf $module $slot $port $number" . $this->line_br;
						
						#slot
						$slot_interval = explode("-", $slot);
						if (count($slot_interval) == 1) {
							$slot_interval[1] = $slot_interval[0];
						}
						
						#port
						$port_interval = explode("-", $port);
						if (count($port_interval) == 1) {
							$port_interval[1] = $port_interval[0];
						}
						
						#number increment
						if (strpos($number, '+')) {
							$number_incremment = 1;
							$number = rtrim($number,"+");
						}
						elseif (strpos($number, '-')) {
							$number_incremment = -1;
							$number = rtrim($number,"-");
						}
						else {
							$number_incremment = 0;
						}
						
						
						for ($s = $slot_interval[0]; $s <= $slot_interval[1]; $s++) {
							for ($p = $port_interval[0]; $p <= $port_interval[1]; $p++) {
								//print $deb++ . ". CONF=$conf MODULE=$module SLOT=$s PORT=$p NUMBER=$number //". $this->arr_module_name[$module] . $this->line_br;
								#Условие заполнения массива по номеру конфигурации
								if ($number_conf == $conf) {
									$this->arr_tunes[$number]['module'] = $module;
									$this->arr_tunes[$number]['slot'] = $s;
									$this->arr_tunes[$number]['port'] = $p;
								}
								$number = $number+$number_incremment ;
							}
						}
					}
				}
			}
		}
		
		
		$count_row_file_tunes = count($this->arr_tunes);
		if ($count_row_file_tunes == 0) {
			$this->_Error("Number(s) not found");
			return false;
		}
		else {
			$this->Log("Time read: " . (microtime(1) - $start_read_file_tunes) . "; Number(s): $count_row_file_tunes");
		}
		//print_r($this->arr_tunes); #DEBUG
		return true;
	}

	/**
	 * Cоздание массива номер->модуль из файла настроек SMPАдминистратора
	 */
	function Parse_file_SMPAdmin_win($file_tunes = '') {

		if (trim($file_tunes) == '') {
			$this->_Error("Parse_file_SMPAdmin_win.file_tunes == NULL");
			return false;
		}
		
		$this->arr_tunes = array();
		
		$this->Log("Parse_file_SMPAdmin_win(\$file_tunes='$file_tunes')...");
		
		$start_read_file_tunes = microtime(1);
		
		if (!$handle = fopen($file_tunes, 'r')) {
			$this->_Error("Could not open input file: '$file_tunes'");
			return false;
		}
		
		while (!feof($handle)) {
			$buffer = fgets($handle, 4096);
			//$buffer = iconv('cp1251', 'UTF-8', $buffer);
			$mas = explode(';',$buffer);
			#проверка на структуру
			if (count($mas) == 44 and preg_match("#^(\d{1,7})$#", $mas[0])) {
				$this->arr_tunes[$mas[0]]['module'] = $mas[4];
				$this->arr_tunes[$mas[0]]['slot'] = $mas[5];
				$this->arr_tunes[$mas[0]]['port'] = $mas[6];
			}
		}
		fclose($handle);
		
		$count_row_file_tunes = count($this->arr_tunes);
		if ($count_row_file_tunes == 0) {
			$this->_Error("Number(s) not found");
			return false;
		}
		else {
			$this->Log("Time read: " . (microtime(1) - $start_read_file_tunes) . "; Number(s): $count_row_file_tunes");
		}
		//print_r($this->arr_tunes); #DEBUG
		return true;
	}

	/**
	 * Узнать номер модуля по номеру телефона
	 */
	function Get_number_module($number = '') {
		$this->Log("Get_number_module(\$number = '$number')...");
		
		if (trim($number) == '') {
			$this->_Error("Get_number_module.number == NULL");
			return false;
		}	
		
		if (isset($this->arr_tunes[$number]['module'])) {
			return $this->arr_tunes[$number]['module'];
		}
		else {
			if (count($this->arr_tunes) == 0) {
				$this->_Error("Use method: Parse_file_SMPCFG() or Parse_file_SMPAdmin_win()");
			}
			return false;
		}
	}
	
	/*
	 * @author: dken
	 *
	 * @param string $bytes
	 * @param string $prestr
	 */
	function Trace_HEX($bytes, $prestr = 'Trace_HEX:') {
		if ($bytes == '') {
			$res = ' NULL';
		}
		else {
			$res='';
			for($i=0;$i<strlen($bytes);$i++) {
				$res .= sprintf(" %02X",ord($bytes[$i]));
			}
		}
		return $prestr . $res;
	}

	/**
	 * Журнал
	 *
	 * @param string $str
	 */
	function Log($str) {
		$this->ConsoleEcho("[:)] $str");
	}

	/**
	 * Ошибки
	 *
	 * @param string $msg
	 */
	function _Error($str) {
		$this->ConsoleEcho("[:(] $str");
	}

	/**
	 * Вывод
	 *
	 * @param string $str
	 */
	function ConsoleEcho($str) {
		if ($this->console) {
			//print date("Y-m-d H:i:s");
			echo "$str" . $this->line_br;
		}
	}

	/**
	 * #Проверка, консоль или браузер
	 *
	 *
	 */
	function CheckBrowser() {
		if (!(isset($_SERVER["argv"]))) {
			$this->line_br = "<br/>";
		}
	}

	/**
	 * Вывод отладочной информации
	 *
	 * @param $value
	 */
	function Console($value = true) {
		$this->console = $value;
		$this->Log("(UTF-8) Console = $value");
		$this->Log(PROJECT_ . ' | ' . VERSION_ . ' | ' . DATE_);
		$this->Log("SCRIPT_FILENAME='" . $_SERVER["SCRIPT_FILENAME"] . "'");
	}
}
?>
