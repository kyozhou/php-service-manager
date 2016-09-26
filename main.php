<?php
error_reporting(E_ALL);
ini_set( 'display_errors', 'On');
global $argv;
//$psm_command = @$argv[1] ?? 'start';
if(!empty($argv[1])) {
    if(@$argv[0] == 'main.php') {
        die('don\'t use main.php');
    }
    list($psm_file, $psm_ext) = explode('.', @$argv[0]);
    $psm_command = $argv[1];
    global $pidFileName;
    global $scriptName;
    $pidFileName = $psm_file . '.pid';
    $scriptName = $argv[0];
    $psm_pid = @file_get_contents($pidFileName);
    if(empty($psm_pid) && $psm_command == 'start') {
        psm_start($pidFileName);
    }elseif(!empty($psm_pid) && $psm_command == 'stop') {
        psm_stop($psm_pid);
    }elseif(!empty($psm_pid) && $psm_command == 'restart') {
        psm_stop($psm_pid);
        sleep(3);
        psm_start();
    }else {
        die("\ncommand error or process has started. \nphp main.php start|stop|restart\n");
    }
    file_put_contents("/tmp/logger.log", "\nsub\n", FILE_APPEND);
    die;
}else {
    //continue;
    //echo 'processing...';
    //file_put_contents("/tmp/logger.log", "\nsub\n", FILE_APPEND);
}

function psm_start() {
    global $pidFileName;
    //$process = new swoole_process('psm_callback_function', true);
    $process = new swoole_process('psm_callback_function');
    $pid = $process->start();
    swoole_process::daemon(true, false);
    //$result = swoole_process::wait(true);//blocking
    /*if($result !== false) {
        unlink($pidFileName);
    }*/
    /*swoole_process::signal(SIGCHLD, function($sig) {
        //必须为false，非阻塞模式
        file_put_contents("/tmp/logger.log", "$sig\n", FILE_APPEND);
        while($ret =  swoole_process::wait(false)) {
            echo "PID={$ret['pid']}\n";
            file_put_contents("/tmp/logger.log", "PID={$ret['pid']}\n", FILE_APPEND);
        }
    });*/
}

function psm_callback_function(swoole_process $worker)
{
    global $pidFileName;
    global $scriptName;
    $worker->exec('/usr/bin/php', [$scriptName]);
    $worker->signal(SIGTERM, function($sig) {
        global $pidFileName;
        unlink($pidFileName);
    });
    file_put_contents($pidFileName, $worker->pid);
    echo $worker->pid . " started\n";
}

function psm_stop($pid) {
    swoole_process::kill($pid, SIGTERM);
    global $pidFileName;
    unlink($pidFileName);
    echo "$pid stoped\n";
}

