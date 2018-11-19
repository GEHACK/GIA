<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\Dj\User;
use App\Models\Script;
use Carbon\Carbon;

class SshJob extends Job {
    private $depl;
    private $cmd;

    public function __construct(Deployment $depl, String $cmd) {
        $this->depl = $depl;
        $this->cmd = $cmd;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $s = $this->depl->scripts()->create(["name" => "SSH Job - " . Carbon::now(), "type" => "absolute", "value" => ""]);
        error_reporting(E_ALL);
        $pk = \Helpers::getKey(false, false);

        $s->status = 'running';
        $s->save();

        try {

            $pip = $this->depl->proxy_ip;
            $ip = $this->depl->ip;

            $cmd = "eval `ssh-agent`; ssh-add $pk;\n ssh -o StrictHostKeyChecking=no -t -A -i $pk root@$pip ssh -o StrictHostKeyChecking=no -A -v root@$ip /bin/bash << EOT
$this->cmd
EOT

kill \$SSH_AGENT_PID

";

            $lres = ($this->liveExecuteCommand($cmd));

            $s->status = 'finished';
            $s->result = implode($lres);
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
