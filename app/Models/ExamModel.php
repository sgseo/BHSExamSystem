<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExamModel extends Model
{
    protected $tableName='exam';
    protected $table='exam';
    protected $primaryKey = 'eid';
    const DELETED_AT=null;
    const UPDATED_AT=null;
    const CREATED_AT=null;

    public function list($uid)
    {
        $list=DB::table($this->tableName)->where(["available"=>1])->orderBy('exam_due', 'asc')->get()->all();
        foreach($list as &$l){
            $l["score"]=DB::table("test")->where(["eid"=>$l["eid"],"uid"=>$uid])->orderBy('score', 'desc')->first()["score"];
        }
        return $list;
    }

    public function getHistory($eid,$uid)
    {
        return DB::table("test")->where(["eid"=>$eid,"uid"=>$uid])->where(function ($query) {
            $query->where("due_time","<",date("Y-m-d H:i:s"))->orWhere('score', '<>', -1);
        })->orderBy('due_time', 'desc')->select("tid","due_time as time","score")->limit(10)->get()->all();
    }

    public function detail($eid, $uid)
    {
        $basic=DB::table($this->tableName)->where(["available"=>1,"eid"=>$eid])->first();
        if(empty($basic)){
            return null;
        }
        $basic["score"]=DB::table("test")->where(["eid"=>$basic["eid"],"uid"=>$uid])->orderBy('score', 'desc')->first()["score"];
        return $basic;
    }

    public function basic($eid)
    {
        return DB::table($this->tableName)->where(["eid"=>$eid])->first();
    }

    public function startTest($eid,$uid)
    {
        $basic=DB::table("test")->where(["eid"=>$eid,"uid"=>$uid,"score"=>-1])->where("due_time",">",date("Y-m-d H:i:s"))->first();
        return empty($basic)?$this->generateTest($eid,$uid):$basic["tid"];
    }

    public function generateTest($eid,$uid)
    {
        $problemSet=DB::table("problem")->where(["eid"=>$eid])->get()->all();
        $randomProb=array_rand($problemSet,50);
        $tid=DB::table("test")->insertGetId([
            'uid'=>$uid,
            'eid'=>$eid,
            'score'=>-1,
            'due_time'=>date("Y-m-d H:i:s", time()+1800)
        ]);
        $i=1;
        foreach($randomProb as $r){
            DB::table("test_problem")->insert([
                'tid'=>$tid,
                'pid'=>$problemSet[$r]["pid"],
                'pcode'=>$i,
                'cur_score'=>-1,
            ]);
            $i++;
        }
        return $tid;
    }
}
