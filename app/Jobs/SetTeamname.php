<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\Dj\User;
use App\Models\Script;
use Carbon\Carbon;

class SetTeamname extends Job
{
    private $depl;

    public function __construct(Deployment $depl)
    {
        $this->depl = $depl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $s = $this->depl->scripts()->create(["name" => "Teamname enforcement - " . Carbon::now(), "type" => "absolute"]);
        error_reporting(E_ALL);
        $pk = \Helpers::getKey(false, false);

        $s->status = 'running';
        $s->save();

        $pip = $this->depl->proxy_ip;
        $ip = $this->depl->ip;
        $time = time();

        $user = User::find($this->depl->userid);
        if (is_null($user) || is_null($user->team)) {
            echo "Could not deploy, user/team not found";
            return;
        }

        $tn = $user->team->name;

        $cmd = "eval `ssh-agent`; ssh-add $pk;\n ssh -o StrictHostKeyChecking=no -t -A -i $pk root@$pip ssh -o StrictHostKeyChecking=no -A -v root@$ip /bin/bash << EOT

/usr/bin/chfn -f \"$tn\" contestant

cat > /usr/share/cups/banners/pixie << EOF
#PDF-BANNER
Template default.pdf
Show time-at-creation time-at-processing job-originating-user-name

Please bring this to: $tn
EOF

lpadmin -p Printer -P /root/printer.ppd.gz -v ipp://10.1.0.1/ -o job-sheets-default=pixie,none -E

sleep 0.5

service lightdm restart
";

        var_dump($this->liveExecuteCommand($cmd));

        $s->status = 'finished';
        $s->result = $tn;
        $s->save();

        echo $time;
    }

    function liveExecuteCommand($cmd)
    {
        while (@ ob_end_flush()); // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = "";
        $complete_output = "";

        while (!feof($proc))
        {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return array (
            'exit_status'  => intval($matches[0]),
            'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
        );
    }
}
