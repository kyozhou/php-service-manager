<?php
die('test ok');die;
$command = @$argv[1] ?? 'start';
$pid = @file_get_contents('logger.pid');
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
    file_put_contents('logger.pid', $pid);
    swoole_process::daemon(false, false);
    swoole_process::wait();
    echo "$pid started\n";
}

function stop($pid) {
    swoole_process::kill($pid, SIGTERM);
    unlink('logger.pid');
    echo "$pid stoped\n";
}

function callback_function(swoole_process $worker)
{
    $worker->exec('/usr/bin/php', ['logger.php']);
}
