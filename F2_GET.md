
```
<?php
#UTF-8

#Зона
date_default_timezone_set('Asia/Yekaterinburg');

#Выводить ошибки PHP
error_reporting(E_ALL|E_STRICT);

#####_При_работе_через_SCOMM_#####
require('PHPSMPClass/PHPSMPClass.php');

$SMP = new PHPSMPClass();

#Вывод консоли. Console(1)
$SMP->Console(1);

#Подключение
if(!($SMP->Connect('<host>', <port>))) {
    print "!> Connect - ERROR\n";
} else {
    #Включние бинарного режима
    if(!$SMP->Binmode('100100', 3)) {
        print "!> Binmode - ERROR\n";
    } else {
        #Вывести массив карточки абонента
        print_r($SMP->F2_GET('<номер>', <модуль>, 3));
    }
    #Закрываем соединение
    $SMP->Close();
}

?>
```