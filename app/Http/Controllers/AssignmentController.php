<?php

namespace App\Http\Controllers;

use App\Jobs\SetTeamname;
use App\Models\Deployment;
use App\Models\Dj\Contest;
use App\Models\Contest as pContest;
use App\Models\Dj\Team;
use App\Models\Dj\User;
use Eventix\Http\CrudController;
use Illuminate\Http\Request;

class AssignmentController extends CrudController {

    protected static $name = 'assign';

    protected static $routes = [
        "/forceall"    => ["reapplyAssignments" => "POST"],
        "/force/{uid}" => ["reapplyAssignment" => "POST"],
        "/fix[/{cid}]" => ["fixAssignment" => "GET"],
        "/{ip}/{tid}"  => ["assign" => "GET"],
    ];

    protected static $blacklist = self::map;

    public function assign($deplId, $uid) {
        $depl = Deployment::find($deplId);
        $udepl = Deployment::where('userid', $uid)->first();

        if (is_null($depl))
            return response('No deployment found', 404);

        if (!is_null($udepl) && $depl->id == $udepl->id)
            return response('Team already assigned', 202);

        $ouser = User::find($depl->userid);

        $user = User::find($uid);
        if (is_null($user))
            return response('No user found', 404);


        $depl->userid = $user->userid;
        $user->password = "ip:" . $depl->ip;

        if (!is_null($udepl)) {
            $udepl->userid = null;
            $udepl->save();
        }

        if (!is_null($ouser)) {
            $ouser->password = "unset";
            $ouser->save();
        }

        $depl->save();
        $user->save();

        $this->dispatch(new SetTeamname($depl));

        return response('', 204);
    }

    public function fixAssignment($cid = 1) {
        $depls = Deployment::whereNull("userid")->get();

        if ($depls->count() == 0)
            return response('No deployments left', 202);

        $cids = \DB::table('deployments')->whereNotNull('userid')->pluck('userid')->toArray();

        $users = User::select('user.*')->join('contestteam as ct', 'ct.teamid', '=', 'user.teamid')
            ->where("ct.cid", $cid)
            ->whereNotNull('user.teamid')
            ->whereNotIn('user.userid', $cids)
            ->get();

        $users = $users->random(min($users->count(), $depls->count()));

        if ($users->count() == 0)
            return response('No users found', 202);

        for ($i = 0; $i < $users->count(); $i++) {
            $depls[$i]->userid = $users[$i]->userid;
            $users[$i]->password = "ip:" . $depls[$i]->ip;

            $depls[$i]->save();
            $users[$i]->save();

            $this->dispatch(new SetTeamname($depls[$i]));
        }

        return response('', 204);
    }

    public function reapplyAssignments($cid = 1) {
        $depls = Deployment::whereNot("userid")->get();

        foreach ($depls as $depl) {
            $this->dispatch(new SetTeamname($depls[$i]));
        }

        return response('', 204);
    }

    public function reapplyAssignment($uid) {
        $depl = Deployment::where("userid", $uid)->first();

        $this->dispatch(new SetTeamname($depl));

        return response('', 204);
    }
}
