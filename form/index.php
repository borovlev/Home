<?php
error_reporting(E_ALL);

session_start();

define('DATA_FILE', 'messages.txt');
define('FLASH_KEY', 'flash_message');
define('FLASH_STYLE', ''); // добавил новую константу
// functions

// todo : определять в какой цвет закрасить сообщение в зависимости от успеха/фейла
function setFlash($nameSet, $message)
{
    $_SESSION[$nameSet] = $message ;
}

function getFlash($nameGet)
{
    if (!isset($_SESSION[$nameGet])) {
        return null;
    }
    
    $message = $_SESSION[$nameGet];
    unset($_SESSION[$nameGet]);
    
    return $message;
}


function createMessage($username, $email, $message)
{
    $id = uniqid();
    return compact('username', 'email', 'message');
}

function redirect($to) 
{
	session_destroy(); // если форма правильная и капчи совподают 
	session_start();	// новая сессия для того чтобы сгенирировать новую капчу
	setFlash('FLASH_STYLE', 'ok');	// + сообщение передается как второй аргумент
	setFlash('FLASH_KEY', 'Your message was sent');
    header("Location: {$to}");
    die;
}

function requestPost($key, $default = null)
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function requestGet($key, $default = null)
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function isRequestPost()
{
    return (bool) $_POST;
}

function isFormValid()
{
    return 	trim(requestPost('username')) != '' &&
			trim(requestPost('email')) != '' && 
			trim(requestPost('message')) != '' &&
			$_SESSION['captcha_number'] == trim(requestPost('security_number'));  // проверка капчи 
}


// todo: argument for filename
function saveMessage(array $message)
{
    $s = serialize($message);
    file_put_contents(DATA_FILE, $s . PHP_EOL, FILE_APPEND);
}

function loadMessages()
{
    $messages = file_get_contents(DATA_FILE);
    $messages = explode(PHP_EOL, $messages);
    
    foreach ($messages as $key => $message) {
        if ($message) {
            $messages[$key] = unserialize($message);
            continue;
        }
        unset($messages[$key]);
    }
    
    return $messages;
}


// logic

$flashMessage = requestGet('flash');

// todo: delete
if (requestGet('action') == 'delete') {
 
 
 // delete script
 // redirect + flash message
 
    
}

if (isRequestPost()) {
    // todo: добавить проверку капчи, задавать соответствующее значение для сообщения + менять капчу если успех
    if (isFormValid()) {
        $message = createMessage(requestPost('username'), requestPost('email'), requestPost('message'));
        saveMessage($message);
        redirect("../form/"); //get ok Style and message
    } else
	{
    
    setFlash('FLASH_STYLE', 'fail');
    setFlash('FLASH_KEY', 'Fill the fields');
	}
	
}


$messages = loadMessages();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form</title>
    <style> /* добавлены стили в зависимости от переданного сообщения */
	.ok {
    border-style: solid; 
	border-color: green;
   }
   .fail {
	border-style: solid;  
	border-color: red;
   }
	</style>
</head>
<body>
    
    <h1>Form</h1>
    <b class="<?=getFlash('FLASH_STYLE'); ?>"><?=getFlash('FLASH_KEY'); ?></b>
    
    <form method="post">
        <input type="name" name="username" value="<?=requestPost('username') ?>"><br>
        <input type="email" name="email" value="<?=requestPost('email') ?>"><br>
        <textarea name="message"><?=requestPost('message') ?></textarea><br>
        <img src="./captcha.php"> <br>
        <input type="text" name="security_number" /><br>
        <button>GO</button>
    </form>
    
    <hr>
    
    <?php foreach ($messages as $key => $message) : ?>
        
        <i><?=$message['username']?></i><br>
        <?=$message['message']?>
        
        <a href="index.php?action=delete&amp;id=<?=$key?>">Delete</a>
    <hr>
    <?php endforeach ?>
    
    
</body>
</html>
