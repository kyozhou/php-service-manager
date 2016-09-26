<?php
//TODO add signal to auto delete pid file
//TODO add multi process support
error_reporting(E_ALL);
ini_set( 'display_errors', 'On');
global $argv;
//$psm_command = @$argv[1] ?? 'start';

if(!empty($argv[1]) && $argv[1] == 'execute') {
    //do script
}else {
    if(@$argv[0] == 'main.php') {
        die('don\'t use main.php');
    }
    list($psm_file, $psm_ext) = explode('.', @$argv[0]);
    $psm_command = @$argv[1] ?? 'start';
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
    die;
}

function psm_start() {
    global $pidFileName;
    //$process = new swoole_process('psm_callback_function', true);
    $process = new swoole_process('psm_callback_function');
    $pid = $process->start();
    //swoole_process::daemon(false, false);
    //$result = swoole_process::wait(true);//blocking
    /*if($result !== false) {
        unlink($pidFileName);
    }*/
}

function psm_callback_function(swoole_process $worker)
{
    global $scriptName, $pidFileName;
    file_put_contents($pidFileName, $worker->pid);
    echo $worker->pid . " started\n";
    /*swoole_process::signal(SIGTERM | SIGINT | SIGKILL | SIGCHLD, function($sig) {
        file_put_contents("/tmp/logger.log", "signal term\n", FILE_APPEND);
        global $pidFileName;
        unlink($pidFileName);
    });*/
    $worker->exec('/usr/bin/php', [$scriptName, 'execute']);
}

function psm_stop($pid) {
    swoole_process::kill($pid, SIGTERM);
    global $pidFileName;
    unlink($pidFileName);
    echo "$pid stoped\n";
}

