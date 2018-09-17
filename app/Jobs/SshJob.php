<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\Dj\User;

class SshJob extends Job {
    private $depl;
    protected $command = "";

    public function __construct(Deployment $depl, $command) {
        $this->depl = $depl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        error_reporting(E_ALL);
        $pk = \Helpers::getKey(false, false);

        $pip = $this->depl->proxy_ip;
        $ip = $this->depl->ip;
        $time = time();

        $user = User::find($this->depl->userid);
        if (is_null($user) || is_null($user->team)) {
            echo "Could not deploy, user/team not found";
            return;
        }

        $tn = $user->team->name;
        $tid = $user->team->teamid;

        $rand = str_random(32);
        $sshBase = "eval `ssh-agent`; ssh-add $pk;\n ssh -v -A -t -i $pk root@$pip ssh -A -v root@$ip";
        $res = $this->liveExecuteCommand("$sshBase /bin/bash << EOT
/usr/sbin/service lightdm restart
/bin/tar czf /root/$rand.tar.gz /home/contestant/
EOT
");

        $this->liveExecuteCommand("scp -o ProxyCommand=\"ssh -A -t root@$pip nc $ip 22\" root@$ip:/root/$rand.tar.gz " . storage_path()."/$tn-$tid-$rand.tar.gz");

        $this->liveExecuteCommand("$sshBase /bin/bash << EOT
rm -rf /home/contestant/
cp -r /etc/skel /home/contestant
rm -f /root/$rand.tar.gz
chown contestant:contestant /home/contestant/ -R
reboot
EOT
");

        dd($res);
        echo $time;
    }

    function liveExecuteCommand($cmd) {
        while (@ ob_end_flush()) ; // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output = "";
        $complete_output = "";

        while (!feof($proc)) {
            $live_output = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return [
            'exit_status' => intval($matches[0]),
            'output'      => str_replace("Exit status : " . $matches[0], '', $complete_output),
        ];
    }
}
