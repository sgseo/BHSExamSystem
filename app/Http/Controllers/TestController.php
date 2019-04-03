<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TestModel;
use Illuminate\Http\Request;
use Auth;


class TestController extends Controller
{
    public function detail($tid)
    {
        $testModel= new TestModel();
        $testInfo=$testModel->basic($tid);
        if(is_null($testInfo)){
            return Redirect::route('home');
        }
        if($testInfo["uid"]!=Auth::user()->id){
            return Redirect::route('home');
        }
        $testInfo["end"]=$testInfo["remaining"]<0;
        $testProb=$testModel->getProb($tid);
        return $testInfo["end"]?view('test.complete', [
            'page_title'=>"测试结果",
            'site_title'=>"贝尔英才学院诚信考试系统",
            'navigation' => "Home",
            "testInfo"=>$testInfo
        ]):view('test.detail', [
            'page_title'=>"测试",
            'site_title'=>"贝尔英才学院诚信考试系统",
            'navigation' => "Home",
            "testProb"=>$testProb,
            "testInfo"=>$testInfo
        ]);
    }
}
