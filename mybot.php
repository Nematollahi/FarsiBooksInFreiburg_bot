<?php

include("Telegram.php");
header('Content-Type: text/html; charset=utf-8');
header('Content-Type: text/plain');


function utf8 ( $codepoint ) {
    // 200E = ltr mark
    // 200F = rtl mark
    return json_decode('"\u'.$codepoint.'"');
}

function rtl ( $string ) {
    return utf8('200f').$string.utf8('200e');
}

function convert_to_persian($string) {
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    $convertedPersianNums = str_replace($english, $persian, $string);

    return $convertedPersianNums;
}

function convert_to_english($string) {
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    $convertedEnglishNums = str_replace($persian, $english, $string);

    return (int)$convertedEnglishNums;
}

$config = parse_ini_file('../config.ini');

$conn = new mysqli('localhost',$config['username'],$config['password'],$config['dbname']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

$sql = "SET NAMES 'utf8'";
$conn->query($sql);
$sql = "SET CHARACTER SET 'utf8'";
$conn->query($sql);
$sql = "SET character_set_connection = 'utf8'";
$conn->query($sql);

date_default_timezone_set("asia/tehran");
// Set the bot TOKEN
$bot_id = $config['bot_id'];
// Instances the class
$telegram = new Telegram($bot_id);
$text = $telegram->Text();
$username = $telegram->Username();
$first_name = $telegram->FirstName();
$last_name = $telegram->LastName();
$message_id = $telegram->MessageID();
$user_id = $telegram->UserID();
$chat_id = $telegram->ChatID();
$keyboard_1 = [
    ["فهرست کتاب‌های فارسی موجود در فرایبورگ"],
    ["اضافه کردن کتاب به فهرست"],
    ["راهنما" ,"کتاب‌های من"]
];

$keyboard_2 = [
    ["بازگشت"]
];
$keyboard_3 = [
  ["ثبت/ویرایش نام مترجم" ,"ثبت/ویرایش نام نویسنده" ,"ثبت/ویرایش نام کتاب"],
  ["ثبت نهایی"],
  ["لغو"]
];
$keyboard_4 = [
    ["لغو"]
];
$keyboard_5 = [
  ["حذف کتاب از فهرست"],
  ["بازگشت"]
];
$optionalname = "...";
$time = "...";
$place = "...";
$Book_name = "...";
$Author = "...";
$state = 0;
$registered = 0;
$max_registered = 0;
$confirmed = 0;
$intro = "برای پر کردن فرم زیر، لطفا پس از بازکردن صفحه‌کلید این بات، از دکمه‌های«ثبت/ویرایش نام»، «ثبت/ویرایش زمان»، «ثبت/ویرایش مکان» و «ثبت/ویرایش شرح» استفاده کنید. در انتها در صورت تایید اطلاعات وارد شده، از دکمه «ثبت نهایی» و یا در صورت انصراف از دکمه «لغو» استفاده کنید.";
$which_book = "لطفا نام کتاب خود را وارد کنید. در صورت انصراف از دکمه «لغو» استفاده کنید.";
$which_number = "لطفا شماره کتابی را که مایل به حذف آن هستید وارد کنید.";
$number = "شماره: ...";
$which_author = "لطفا نام نویسنده کتاب را وارد کنید. در صورت انصراف از دکمه «لغو» استفاده کنید.";
$which_field = "یکی را وارد کنید. در صورت انصراف از دکمه «لغو» استفاده کنید.";
$welcome = "به بات کتاب‌های فارسی موجود در فرایبورگ خوش آمدید!";
$no_entry = "شما در فهرست کتاب‌های فارسی موجود در فرایبورگ کتابی ندارید.";
$about = "سلام😊🌿\r\n \r\n📚 شاید برای شما هم پیش اومده باشه که دلتون بخواد کتابی رو که به زبان فارسی نوشته یا ترجمه شده در دست بگیرید و بخونید! اما به این خاطر که در فرایبورگ زندگی می‌کنید امکان تهیه چنین کتابی براتون میسر نبوده باشه... یا شاید با خودتون گفته باشید این بار که برم ایران کلی کتاب جدید با خودم میارم ولی سنگین بودن کتاب‌ها و محدودیت بار این فرصت رو از شما گرفته باشه.\r\n \r\n🎯 هدف از راه‌اندازی این بات تلگرام اینه که بتونیم فهرست جامعی از کتاب‌های فارسی موجود در فرایبورگ تهیه کنیم و کتاب‌هایی رو که هر کدوم از ما با خودمون از ایران آوردیم با هم به اشتراک بگذاریم.\r\n \r\n🔹گزینه «فهرست کتاب‌های فارسی موجود در فرایبورگ»\r\nبا انتخاب این گزینه، فهرستی شامل نام کتاب‌ها، به همراه نام نویسنده و نام یا نام کاربری مالک کتاب‌ بهتون نمایش داده میشه. برای به امانت گرفتن هر کتاب،‌ شما باید به صورت مستقیم با مالک کتاب ارتباط برقرار کنید و در صورت در دسترس بودن کتاب، طی قراری حضوری شخصا کتاب رو دریافت کنید.\r\n \r\n🔹گزینه «اضافه کردن کتاب به فهرست»\r\nدر صورتی که مایل هستید کتاب‌های فارسی خودتون رو با بقیه به اشتراک بگذارید، می‌تونید این گزینه رو انتخاب کنید و نام کتاب و نام نویسنده کتاب رو وارد کنید.\r\n \r\n🔹گزینه «کتاب‌های من»\r\nبا انتخاب این گزینه، کتاب‌هایی که توسط شما به فهرست اضافه شده‌ بهتون نمایش داده میشه.\r\n▫️در صورتی که مایل به حذف کتاب خودتون از فهرست هستید می‌تونید از گزینه «حذف کتاب از فهرست» استفاده کنید.\r\n \r\nاگر سوال، پیشنهاد یا انتقادی دارین خوشحال می‌شیم که باهامون در میون بگذارید:\r\n@Kiana_Far\r\n@Iman_Nematollahi";
$sql = "SELECT `ID`, `Chat_ID`, `Username`, `First_name`, `Last_name`, `Book_name`, `Author`, `State`, `Registered`, `Confirmed` FROM records WHERE Chat_ID = $chat_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $registered = $row["Registered"];
        if ($registered >= $max_registered) {
          $max_registered = $registered;
          $state = $row["State"];
          $Book_name = $row["Book_name"];
          $Author = $row["Author"];
          $confirmed = $row["Confirmed"];
        }
      }
}

if($state == 0) {
  if(($text == "/start") && !is_null($chat_id)) {
      $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
      $content = array('chat_id' => $chat_id,'text' => $welcome, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);

  }
  elseif (($text == "راهنما") && !is_null($chat_id)) {
      $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
      $content = array('chat_id' => $chat_id,'text' =>  $about, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
  }
  elseif (($text == "اضافه کردن کتاب به فهرست") && !is_null($chat_id)) {
      $reply_markup = $telegram->buildKeyBoard($keyboard_3, true, true, true);
      $content = array('chat_id' => $chat_id,'text' =>  $which_book, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
      $state = 1;

      $registered = $max_registered + 1;
      $confirmed = 0;
      $sql = "INSERT INTO records (`Chat_ID`, `Username`, `First_name`, `Last_name`, `Book_name`, `Author`, `State`, `Registered`, `Confirmed`)
      VALUES ($chat_id, '$username', '$first_name', '$last_name', '$Book_name', '$Author', $state, $registered, $confirmed)";
      $result = $conn->query($sql);

      $Book_name = "...";
      $reply_markup = $telegram->buildKeyBoard($keyboard_4, true, true, true);
      $final ="کتاب: {$Book_name} ";
      $content = array('chat_id' => $chat_id,'text' =>  $final, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
  }
  elseif (($text == "فهرست کتاب‌های فارسی موجود در فرایبورگ") && !is_null($chat_id)) {

      $sql="select * FROM records ORDER BY Book_name ASC";
      $result = $conn->query($sql);
      $final = "نام کتاب"." | "."نویسنده"." | "."مالک کتاب"."\r\n";
      $final .= rtl("----------------------------------------")."\r\n";
      $count = 0;
      while($results =mysqli_fetch_array($result)){
        $count += 1;
        $final .= convert_to_persian((string)$count);
        $final .= ". ";
        if (!empty($results['Username'])) {
          $final .= $results['Book_name']." | ".$results['Author']." | "."@".$results['Username']."\r\n";
        }
        else {
          $final .= $results['Book_name']." | ".$results['Author']." | ".$results['First_name']." ".$results['Last_name']."\r\n";
        }
        if ($count % 50 == 0) {
                $final .= rtl("📚")."\r\n";
                $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
                $content = array('chat_id' => $chat_id,'text' =>  $final, 'reply_markup' => $reply_markup);
                $telegram->sendMessage($content);
                $final = "نام کتاب"." | "."نویسنده"." | "."مالک کتاب"."\r\n";
                $final .= rtl("----------------------------------------")."\r\n";
        }
      }
      $final .= rtl("📚")."\r\n";

      $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
      $content = array('chat_id' => $chat_id,'text' =>  $final, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
  }
  elseif (($text == "کتاب‌های من") && !is_null($chat_id)) {

      if ($max_registered == 0) {
        $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
        $content = array('chat_id' => $chat_id,'text' =>  $no_entry, 'reply_markup' => $reply_markup);
        $telegram->sendMessage($content);
      }
      else {
        $sql="select * FROM records WHERE Chat_ID = $chat_id ORDER BY Registered";
        $result = $conn->query($sql);
        $final = "نام کتاب"." | "."نویسنده"." | "."مالک کتاب"."\r\n";
        $final .= rtl("----------------------------------------")."\r\n";

        while($results =mysqli_fetch_array($result)){
          $count = $results['Registered'];
          $final .= convert_to_persian((string)$count);
          $final .= ". ";
          if (!empty($results['Username'])) {
            $final .= $results['Book_name']." | ".$results['Author']." | "."@".$results['Username']."\r\n";
          }
          else {
            $final .= $results['Book_name']." | ".$results['Author']." | ".$results['First_name']." ".$results['Last_name']."\r\n";
          }
        }
        $final .= rtl("📚")."\r\n";
        $reply_markup = $telegram->buildKeyBoard($keyboard_5, true, true, true);
        $content = array('chat_id' => $chat_id,'text' =>  $final, 'reply_markup' => $reply_markup);
        $telegram->sendMessage($content);
      }
  }
  elseif (($text == "حذف کتاب از فهرست") && !is_null($chat_id)) {
      $reply_markup = $telegram->buildKeyBoard($keyboard_4, true, true, true);
      $content = array('chat_id' => $chat_id,'text' =>  $which_number, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
      $content = array('chat_id' => $chat_id,'text' =>  $number, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
      $state = 3;
      $sql = "UPDATE records SET `State`= $state WHERE Chat_ID=$chat_id AND Registered = $max_registered";
      $result = $conn->query($sql);
  }
  elseif ((($text == "بازگشت")) && !is_null($chat_id)) {
      $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
      $content = array('chat_id' => $chat_id, 'text' => $welcome, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
  }
  elseif (($text == "لغو") && !is_null($chat_id)) {
      $sql = "DELETE FROM records WHERE Chat_ID = $chat_id AND Registered = $max_registered";
      $result = $conn->query($sql);
      $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
      $content = array('chat_id' => $chat_id, 'text' =>  $welcome, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
  }
}

elseif ($state == 1) {
    if (($text != "لغو") && !is_null($chat_id)) {
       $sql = "UPDATE records SET `Book_name`= '$text' WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $sql = "UPDATE records SET `Confirmed`= 1 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $sql = "UPDATE records SET `State`= 2   WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);

       $sql = "SELECT `Book_name` FROM records WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         $Book_name = $row["Book_name"];
         $final ="کتاب: {$Book_name} ";
       }
       $reply_markup = $telegram->buildKeyBoard($keyboard_4, true, true, true);
       $content = array('chat_id' => $chat_id, 'text' =>  $final, 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);
       $content = array('chat_id' => $chat_id, 'text' => $which_author, 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);

       $Author = "...";
       $final ="نویسنده: {$Author} ";
       $content = array('chat_id' => $chat_id,'text' =>  $final, 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);

   } elseif (($text == "لغو") && !is_null($chat_id)) {
       $sql = "UPDATE records SET `State`= 0 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $sql = "DELETE FROM records WHERE Chat_ID = $chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
       $content = array('chat_id' => $chat_id, 'text' => $welcome, 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);
   }

}

elseif ($state == 2) {
    if (($text != "لغو") && !is_null($chat_id)) {
       $sql = "UPDATE records SET `Author`= '$text' WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $sql = "UPDATE records SET `Confirmed`= 1 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $sql = "UPDATE records SET `State`= 0 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);

       $sql = "SELECT `Author` FROM records WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         $Author = $row["Author"];
         $final ="نویسنده: {$Author} ";
       }
       $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
       $content = array('chat_id' => $chat_id, 'text' =>  $final, 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);
       $num_registered = convert_to_persian((string)$max_registered);
       $content = array('chat_id' => $chat_id, 'text' => "دوست عزیز کتاب شما به فهرست اضافه شد! شما تاکنون {$num_registered} کتاب به فهرست کتاب‌های فارسی موجود در فرایبورگ اضافه کرده‌اید.", 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);

   } elseif (($text == "لغو") && !is_null($chat_id)) {
       $sql = "UPDATE records SET `State`= 0 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $sql = "DELETE FROM records WHERE Chat_ID = $chat_id AND Registered = $max_registered";
       $result = $conn->query($sql);
       $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
       $content = array('chat_id' => $chat_id, 'text' => $welcome, 'reply_markup' => $reply_markup);
       $telegram->sendMessage($content);
   }

}

elseif ($state == 3) {
  if (($text != "لغو") && !is_null($chat_id)) {
    $text = convert_to_english($text);
    $sql = "UPDATE records SET `State`= 0 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
    $result = $conn->query($sql);
    $sql = "DELETE FROM records WHERE Chat_ID = $chat_id AND Registered = $text";
    $result = $conn->query($sql);

    $sql="select * FROM records WHERE Chat_ID = $chat_id ORDER BY Registered";
    $result = $conn->query($sql);
    $i = 1;
    while($results =mysqli_fetch_array($result)) {
      $j = $results['Registered'];
      $sql = "UPDATE records SET `Registered`= $i WHERE Chat_ID=$chat_id AND Registered = $j";
      $result2 = $conn->query($sql);
      $i += 1;
    }
    $sql="select * FROM records WHERE Chat_ID = $chat_id ORDER BY Registered";
    $result = $conn->query($sql);
    $final = "نام کتاب"." | "."نویسنده"." | "."مالک کتاب"."\r\n";
    $final .= rtl("----------------------------------------")."\r\n";

    while($results =mysqli_fetch_array($result)){
      $count = $results['Registered'];
      $final .= convert_to_persian((string)$count);
      $final .= ". ";
      if (!empty($results['Username'])) {
        $final .= $results['Book_name']." | ".$results['Author']." | "."@".$results['Username']."\r\n";
      }
      else {
        $final .= $results['Book_name']." | ".$results['Author']." | ".$results['First_name']." ".$results['Last_name']."\r\n";
      }
    }
    $final .= rtl("📚")."\r\n";
    $reply_markup = $telegram->buildKeyBoard($keyboard_5, true, true, true);
    $content = array('chat_id' => $chat_id,'text' =>  $final, 'reply_markup' => $reply_markup);
    $telegram->sendMessage($content);
  }
  elseif (($text == "لغو") && !is_null($chat_id)) {
      $sql = "UPDATE records SET `State`= 0 WHERE Chat_ID=$chat_id AND Registered = $max_registered";
      $result = $conn->query($sql);
      $reply_markup = $telegram->buildKeyBoard($keyboard_1, true, true, true);
      $content = array('chat_id' => $chat_id, 'text' => $welcome, 'reply_markup' => $reply_markup);
      $telegram->sendMessage($content);
  }
}

$conn->close();
?>
