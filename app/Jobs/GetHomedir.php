<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\Dj\User;
use App\Models\Script;
use Carbon\Carbon;

class GetHomedir extends Job {
    private $depl;

    public function __construct(Deployment $depl) {
        $this->depl = $depl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $s = $this->depl->scripts()->create(["name" => "Homedir retrieval - " . Carbon::now(), "type" => "absolute"]);
        $s->status = 'running';
        $s->save();

        error_reporting(E_ALL);
        $pk = \Helpers::getKey(false, false);

        try {

            $pip = $this->depl->proxy_ip;
            $ip = $this->depl->ip;
            $time = time();

            $user = User::find($this->depl->userid);
            if (is_null($user) || is_null($user->team)) {
                echo "Could not deploy, user/team not found";

                return;
            }

            $tid = $user->team->teamid;

            $rand = str_random(32);
            echo "Storing into: '$rand'";

            `eval \`ssh-agent\`; ssh-add $pk`;
            $sshBase = "ssh -o StrictHostKeyChecking=no -t -A -i $pk root@$pip ssh -o StrictHostKeyChecking=no -A -v root@$ip";
            $res = $this->liveExecuteCommand("$sshBase /bin/bash << EOT
/usr/sbin/service lightdm restart
/bin/tar --exclude=\".*\" -czvf /root/$rand.tar.gz /home/contestant
");
            $scpCmd = "scp -i $pk -o StrictHostKeyChecking=no -o ProxyCommand=\"ssh -A -i $pk -t -o StrictHostKeyChecking=no root@$pip nc $ip 22\" root@$ip:/root/$rand.tar.gz " . storage_path() . "/$tid-$rand.tar.gz";
            echo $scpCmd;
            var_dump($this->liveExecuteCommand($scpCmd));

            var_dump($this->liveExecuteCommand("$sshBase /bin/bash << EOT
rm -rf /home/contestant/
cp -r /etc/skel /home/contestant
rm -f /root/$rand.tar.gz
chown contestant:contestant /home/contestant/ -R
reboot
"));

            var_dump($res);
            echo $time;

            $s->status = 'finished';
            $s->result = storage_path() . "/$tid-$rand.tar.gz";
            $s->save();
        } catch (\Exception $e) {
            $s->status = 'terminated';
            $s->result = (string)$e;
            $s->save();
        }

        `kill \$SSH_AGENT_PID`;
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
