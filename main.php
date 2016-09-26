<?php
global $argv;
if(@$argv[0] == 'main.php') {
    die('don\'t use main.php');
}
list($psm_file, $psm_ext) = explode('.', @$argv[0]);
$psm_command = @$argv[1] ?? 'start';
$pidFileName = $psm_file . '.pid';
$psm_pid = @file_get_contents($pidFileName);
if(empty($psm_pid) && $psm_command == 'start') {
    psm_start();
}elseif(!empty($psm_pid) && $psm_command == 'stop') {
    psm_stop($psm_pid);
}elseif(!empty($psm_pid) && $psm_command == 'restart') {
    psm_stop($psm_pid);
    sleep(3);
    psm_start();
}else {
    die("command error. \nphp main.php start|stop|restart\n");
}

function psm_start() {
    $process = new swoole_process('psm_callback_function', true);
    $pid = $process->start();
    global $pidFileName;
    file_put_contents($pidFileName, $pid);
    swoole_process::daemon(false, false);
    swoole_process::wait();
    echo "$pid started\n";
}

function psm_stop($pid) {
    swoole_process::kill($pid, SIGTERM);
    global $pidFileName;
    unlink($pidFileName);
    echo "$pid stoped\n";
}

function psm_callback_function(swoole_process $worker)
{
    global $pidFileName;
    $worker->exec('/usr/bin/php', [$pidFileName]);
}
