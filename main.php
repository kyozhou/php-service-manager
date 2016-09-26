<?php
if(@$argv[0] == 'main.php') {
    die('don\'t use main.php');
}
list($file, $ext) = explode('.', @$argv[0]);
$command = @$argv[1] ?? 'start';
$pidFileName = $file . '.pid';
$pid = @file_get_contents($pidFileName);
if(empty($pid) && $command == 'start') {
    start();
}elseif(!empty($pid) && $command == 'stop') {
    stop($pid);
}elseif(!empty($pid) && $command == 'restart') {
    stop($pid);
    sleep(3);
    start();
}else {
    die("command error. \nphp main.php start|stop|restart\n");
}

function start() {
    $process = new swoole_process('callback_function', true);
    $pid = $process->start();
    file_put_contents($pidFileName, $pid);
    swoole_process::daemon(false, false);
    swoole_process::wait();
    echo "$pid started\n";
}

function stop($pid) {
    swoole_process::kill($pid, SIGTERM);
    unlink($pidFileName);
    echo "$pid stoped\n";
}

function callback_function(swoole_process $worker)
{
    $worker->exec('/usr/bin/php', [$pidFileName]);
}
