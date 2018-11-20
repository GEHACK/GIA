<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Models\Dj\User;
use App\Models\Script;
use Carbon\Carbon;

class SetTeamname extends Job {
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
        $s = $this->depl->scripts()->create(["name" => "Teamname enforcement - " . Carbon::now(), "type" => "absolute", "value" => ""]);
        error_reporting(E_ALL);
        $pk = \Helpers::getKey(false, false);

        $s->status = 'running';
        $s->save();

        try {
            $pip = $this->depl->proxy_ip;
            $ip = $this->depl->ip;
            $time = time();

            $user = User::find($this->depl->userid);
            if (is_null($user) || is_null($user->team)) {
                echo "Could not deploy, user/team not found";

                return;
            }

            // TODO determine placement when printing
            $count = \DB::table('deployments')
                ->selectRaw('count(guid) as c')
                ->whereRaw('cast(numerator / denominator as decimal(16,8)) < ?', $this->depl->numerator / $this->depl->denominator)
                ->where('room_id', $this->depl->room_id)
                ->first()->c;

            $po = $this->depl->getRoomPosition();

            $name = trim(str_replace([':', '='], ['᛬', '⹀'], $user->team->name));
            echo "Setting $name\n";

            $chfnMaxLength = 80;
            if (strlen($name) > $chfnMaxLength) {
                $zalgo = new \Zalgo\Zalgo(new \Zalgo\Soul(), \Zalgo\Mood::enraged());
                if (strlen($deZalgoed = $zalgo->soothe($name)) <= $chfnMaxLength) {
                    $name = $deZalgoed;
                } else {
                    $name = mb_strcut($name, 0, $chfnMaxLength);
                }
            }

            $tn = trim($name);
            $un = $user->username;
            $user->team->room = $loc = sprintf("Room: %s, Row: %s, Col: %s", $po["room"], $po["row"], $po["column"]);
            $user->team->save();

            $user->ip_address = $this->depl->ip;
            $user->save();

            $cmd = "eval `ssh-agent`; ssh-add $pk;\n ssh -o StrictHostKeyChecking=no -t -A -i $pk root@$pip ssh -o StrictHostKeyChecking=no -A -v root@$ip /bin/bash << EOT
if /usr/bin/chfn -f \"$tn\" contestant; then
   echo 'Jeej'
else
   /usr/bin/chfn -f \"$un\" contestant
fi

service lightdm restart
EOT

kill \$SSH_AGENT_PID

";

            $lres = ($this->liveExecuteCommand($cmd));

            $s->status = 'finished';
            $s->result = "$tn $count $loc  ".implode($lres);
            $s->save();

            echo $time;
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
