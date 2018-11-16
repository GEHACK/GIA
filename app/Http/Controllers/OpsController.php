<?php

namespace App\Http\Controllers;

use App\Jobs\GetHomedir;
use App\Jobs\SetTeamname;
use App\Models\Deployment;
use App\Models\Dj\Team;
use App\Models\Dj\User;
use App\Models\Script;
use App\Models\Room;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class OpsController extends CrudController {
    protected static $blacklist = self::map;

    protected static $routes = [
        "enforce" => [
            "locUpdate"  => ["updateAllLocations" => "POST"],
            "enforceAll" => "POST",
            "{id}"       => ["enforceOne" => "POST"],
        ],
        "homedir" => [
            "homedirAll" => "POST",
            "{id}"       => ["homedirOne" => "POST"],
        ],
        "exec"    => [
            "execAll"  => "POST",
            "{id}"     => ["execOne" => "POST"],
            "ssh/{id}" => ["ssh" => "GET"],
        ],
    ];

    public function enforceAll(Request $r) {
        $depls = Deployment::whereNotNull("userid")->get();
        if (is_null($depls))
            $this->error(404);

        foreach ($depls as $depl)
            $this->dispatch(new SetTeamname($depl));

        return response('', 204);
    }

    public function homedirAll(Request $r) {
        $depls = Deployment::all();
        foreach ($depls as $depl)
            $this->dispatch(new GetHomedir($depl));

        return response('', 204);
    }

    public function execAll(Request $r) {


    }

    public function enforceOne(Request $r, $id) {
        $depl = Deployment::find($id);
        if (is_null($depl))
            $this->error(404);

        $this->dispatch(new SetTeamname($depl));

        return response('', 204);
    }

    public function homedirOne(Request $r, $id) {
        $depl = Deployment::find($id);
        if (is_null($depl))
            $this->error(404);

        $this->dispatch(new GetHomedir($depl));

        return response('', 204);
    }

    public function execOne(Request $r, $id) {

    }

    public function ssh(Request $r, $id) {
        $deployment = Deployment::find($id);

        if (is_null($deployment))
            $this->error(404, 'Deployment not found');

        $cmd = is_null($deployment->proxy_ip)
            ? "ssh.sh"
            : "sshProxied.sh";

        $pk = \Helpers::getKey(false, false);

        $cmd = sprintf(
            "%s -t --cgi=25000-26000 --user-css Normal:+%s %s -s /:%s:%s:HOME:'%s %s \"%s\" \"%s\"'",
            env("SHELLINABOX", null),
            storage_path(env("SHELLINABOX_STYLE", "white-on-black.css")),
            env("SHELLINABOX_PARAMS", ""),
            env("SHELLINABOX_USER", "root"),
            env("SHELLINABOX_GROUP", "root"),
            storage_path($cmd),
            $pk,
            $deployment->ip,
            $deployment->proxy_ip
        );

        // return `$cmd`;

        // return $cmd;

        return explode("\r\n\r\n", `$cmd`)[1];
    }

    public function updateAllLocations() {
        $depls = Deployment::with('user.team')->whereNotNull('room_id')->get();

        Team::query()->update(['room' => null]);
        User::query()->update(['ip_address' => null]);

        $resp = [];
        foreach ($depls as $depl) {
            if (is_null($depl->user) || is_null($depl->user->team))
                continue;

            $po = $depl->getRoomPosition();
            $depl->user->team->room = $loc = sprintf("%s, %s, %s", $po["room"], $po["row"], $po["column"]);
            $depl->user->ip_address = $depl->ip;

            $resp[] = "$loc&nbsp;&nbsp;&nbsp;=>&nbsp;&nbsp;&nbsp;" . $depl->user->team->name;

            $depl->user->save();
            $depl->user->team->save();
        }

        natsort($resp);
        return implode("<br />", $resp);
    }
}