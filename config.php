<?php 

if(!defined('BASEPATH')){
    exit('Direct access not allowed!');
}


$server_ip = $_SERVER['REMOTE_ADDR'];

if($server_ip == '::1' || $server_ip == '127.0.0.1' || $server_ip == 'localhost'){
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PWD', '');
    define('DB_NAME','thenetup_dashboard');
}else{
    define('DB_HOST', 'sql200.byethost7.com');
    define('DB_USER', 'b7_27692913');
    define('DB_PWD', 'h33kCZ5x6rJSSvm');
    define('DB_NAME','b7_27692913_hrdashboard');
}



    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $current_user_ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $current_user_ip = $forward;
    }
    else
    {
        $current_user_ip = $remote;
    }


    if(isset($_SESSION['user_ip'])){
        $user_ip_session = $_SESSION['user_ip'];
    }
   
if($current_user_ip == '' || empty($current_user_ip)){
    exit('You are not allowed!');
}

if(isset($_SESSION['logged_in']) && !isset($user_ip_session)){
    unset($_SESSION['logged_in']);
    session_destroy();  
    exit('Direct access not allowed!');
}
if(isset($user_ip_session) && empty($user_ip_session)){
    unset($_SESSION['logged_in']);
    session_destroy(); 
    exit('Direct access not allowed!');
}

if(isset($user_ip_session) && !empty($user_ip_session)){
    if($user_ip_session !== $current_user_ip){
        unset($_SESSION['logged_in']);
        unset($_SESSION['user_ip']);
        session_destroy(); 
        exit('Direct access not allowed!');
    }
}
?>