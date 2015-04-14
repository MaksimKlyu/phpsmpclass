*PHP класс для работы с АТС М200*

```
phpsmpclass
Automatically exported from code.google.com/p/phpsmpclass
```

```php
require('PHPSMPClass/PHPSMPClass.php');
$SMP = new PHPSMPClass();

# Подключение к SComm*
$SMP->Connect('127.0.0.1', 10001);

# Включение бинарного режима.
$SMP->Binmode('100100', 3)

# Вывести массив карточки абонента. ('phone',module,timeout)*
print_r($SMP->F2_GET('234567', 8, 3));

/*
Array
(
    [234567] => Array
        (
            [GET] => Array
                (
                    [c1] => 62
                    [c2] => 0
                    [scomm] => 3
                    [scconn] => 2
                    [module] => 8
                    [c6] => 255
                    [net_mes] => 33
                    [c8] => 0
                    [noname9] => 13
                    [number] => 1234567
                    [_c14] => 50
                    [USE] => 1
                    [_c15bit] => 95
                    [_c16bit] => 66
                    [_c17bit] => 8
                    [_c18bit] => 16
                    [SLOT] => 0
                    [HOLE] => 0
                    [AON] => 1234567
                    [CATAON] => 1
                    [REDIRECT] => 
                    [ALARM] => 12:00
                    [PINCODE] => 0000
                    [PRIVLEVEL] => 1
                    [TEI] => 0
                    [C56] => 0
                    [C57] => 0
                    [C58] => 0
                    [C59] => 0
                    [C60] => 2
                    [C61] => 0
                    [C62] => 0
                    [C63] => 0
                    [C64] => 224
                    [module_name] => City_test
                    [c15bit] => Array
                        (
                            [CMN_AON] => 0
                            [CMN_10xxx] => 1
                            [CMN_8xxx] => 0
                            [CMN_PAYSERVICE] => 1
                            [CMN_EXTERNAL] => 1
                            [CMN_INCOME] => 1
                            [CMN_OUTCOME] => 1
                            [CMN_TONE] => 1
                        )

                    [c16bit] => Array
                        (
                            [ENB_AUTODIAL] => 0
                            [ENB_PINCODE] => 1
                            [ENB_INTERCEPT] => 0
                            [ENB_ALARM] => 0
                            [ENB_REDIRECT] => 0
                            [ENB_FLASH] => 0
                            [16_6] => 1
                            [CMN_82xxx] => 0
                        )

                    [c17bit] => Array
                        (
                            [DVO_ALARM] => 0
                            [DVO_REDIRECT_SILENCE] => 0
                            [DVO_REDIRECT_BUSY] => 0
                            [DVO_REDIRECT_ALWAYS] => 0
                            [17_4] => 1
                            [ENB_CALLERID] => 0
                            [ENB_EVILCALL] => 0
                            [ENB_NOTIFICATION] => 0
                        )

                    [c18bit] => Array
                        (
                            [18_0] => 0
                            [DVO_PINCODETWO] => 0
                            [DVO_CLIR] => 0
                            [TYPE_SUBSCRIBER] => 1
                            [DVO_PINCODE] => 0
                            [DVO_NODISTURB] => 0
                            [DVO_NOTIFICATION] => 0
                            [DVO_REDIRECT_HOTCALL] => 0
                        )

                )

            [F2_GET] => TUNE_GETOK
        )
)
*/

# Вывести строку карточки абонента 234567.
print $SMP->F2_GET_STR('234567', 8, 3, 0);

/***************************************
scomm = 3 //Работа через модуль
scconn = 2 //Номер соединения (n+1 com-port, n-9 ethernet)
module = 8 //Модуль
USE = 1 //Включен
SLOT = 0 //Слот
HOLE = 0 //Порт
AON = 1234567 //Номер АОН
CATAON = 1 //Категория АОН
REDIRECT =  //Номер переадресации
ALARM = 12:00 //Время пробуждения
PINCODE = 0000 //PIN-код
PRIVLEVEL = 1 //Уровень привилегий
TEI = 0 //TEI
module_name = City_test //Имя модуля из файла конфигурации
CMN_AON = 0 //Выдача АОНа
CMN_10xxx = 1 //Международная связь
CMN_8xxx = 0 //Междугородняя связь
CMN_PAYSERVICE = 1 //Платные службы
CMN_EXTERNAL = 1 //Внешние номера
CMN_INCOME = 1 //Входящая связь
CMN_OUTCOME = 1 //Исходящая связь
CMN_TONE = 1 //Тональный набор
ENB_AUTODIAL = 0 //Автодозвон
ENB_PINCODE = 1 //Разрешение пинкода
ENB_INTERCEPT = 0 //Вмешательство
ENB_ALARM = 0 //Разрешение будильника
ENB_REDIRECT = 0 //Разрешение переадресации
ENB_FLASH = 0 //Разрешение Flash
CMN_82xxx = 0 //Выход на 82 
DVO_ALARM = 0 //Состояние будильника
DVO_REDIRECT_SILENCE = 0 //Переадресация по неответу
DVO_REDIRECT_BUSY = 0 //Переадресация по занятости
DVO_REDIRECT_ALWAYS = 0 //Переадресация всегда
ENB_CALLERID = 0 //Выдача CallerID 
ENB_EVILCALL = 0 //Злонамеряный вызов
ENB_NOTIFICATION = 0 //Уведомление
DVO_CLIR = 0 //Сокрытие номера
TYPE_SUBSCRIBER = 1 //(0=ANALOG, 1=?)
DVO_PINCODE = 0 //PIN-код на м/г и м/н
DVO_NODISTURB = 0 //Не беспокоить
DVO_NOTIFICATION = 0 //Уведомление
DVO_REDIRECT_HOTCALL = 0 //Горячий вызов
***************************************/

# Записать массив настроек карточек абонента.
print_r($SMP->F2_SET('234567', 8, array('CMN_TONE' => 1,'CMN_OUTCOME' => 1), 3)); //включаем вх. и исх. связь

# Доступные ключи для изменения...
			//'USE' => 1, #0/1
			//'AON' => '1234567', #string
			//'CATAON' => 1, #int
			//'REDIRECT' => '', #string 
			//'ALARM' => '12:00', #string
			//'PINCODE' => '0000', #string
			//'PRIVLEVEL' => 1, #int
			//'TEI' => 0, #int
			#Разрешения
			//'CMN_TONE' => 1, #0/1 (Тональный набор)
			//'CMN_OUTCOME' => 1, #0/1 (Исходящая связь)
			//'CMN_INCOME' => 1, #0/1 (Входящая связь)
			//'CMN_EXTERNAL' => 1, #0/1
			//'CMN_PAYSERVICE' => 1, #0/1
			//'CMN_8xxx' => 0, #0/1
			//'CMN_10xxx' => 1, #0/1
			//'CMN_AON' => 0, #0/1
			#Доступные ДВО
			//'ENB_FLASH' => 0, #0/1
			//'ENB_REDIRECT' => 0, #0/1
			//'ENB_ALARM' => 0, #0/1
			//'ENB_PINCODE' => 1, #0/1 (Pin-код)
			//'ENB_AUTODIAL' => 0, #0/1
			//'ENB_INTERCEPT' => 0, #0/1
			//'ENB_NOTIFICATION' => 0, #0/1
			#Состояние ДВО
			//'DVO_REDIRECT_ALWAYS' => 0, #0/1
			//'DVO_REDIRECT_BUSY' => 0, #0/1
			//'DVO_REDIRECT_SILENCE' => 0, #0/1
			//'DVO_ALARM' => 0, #0/1
			//'DVO_REDIRECT_HOTCALL' => 0, #0/1
			//'DVO_NOTIFICATION' => 0, #0/1
			//'DVO_NODISTURB' => 0, #0/1
			//'DVO_PINCODE' => 0, #0/1
			//'TYPE_SUBSCRIBER' => 0, #0/1 (0=ANALOG, 1=?)
			//'DVO_CLIR' => 0, #0/1
			//'DVO_PINCODETWO' => 0, #0/1
			
# Вывести массив модулей сети GSCP.*
print_r($SMP->F6(1));

#Вывести строку модулей сети GSCP.*
print $SMP->F6_STR(1);

/***************************************
id    # Type Motor Config              Status SComm SCConn Module_name
  1 002 MPB  53507 2011-04-13 09:03:07     OK     3      2 -
  2 003 MPB  53507 2011-04-13 09:03:07     OK     3      2 -
  3 004 MPB  53507 2011-04-13 09:03:07     OK     3      2 -
  4 005 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
  5 006 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
  6 007 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
  7 008 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
  8 009 MAL  53507 2011-04-13 09:03:07     OK     3      2 City_test
  9 010 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 10 012 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 11 013 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 12 014 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 13 015 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 14 016 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 15 017 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 16 018 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 17 019 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 18 020 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 19 021 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 20 022 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 21 023 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
 22 024 MAL  53507 2011-04-13 09:03:07     OK     3      2 -
***************************************/

# Закрываем соединение.
$SMP->Close();
```

```
php -v
# PHP 5.3.3-1ubuntu9.3 with Suhosin-Patch (cli) (built: Jan 12 2011 16:08:14) 
# Copyright (c) 1997-2009 The PHP Group
# Zend Engine v2.3.0, Copyright (c) 1998-2010 Zend Technologies
```
