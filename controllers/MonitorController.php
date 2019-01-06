<?php

namespace app\controllers;

use app\models\ar\View;
use app\models\dbaccess\BootDB;
use app\models\tools\MappingDataTool;
use app\models\tools\PictureDataTool;
use app\models\tools\SaveResultTool;
use Throwable;
use Yii;
use yii\web\Controller;

class MonitorController extends Controller {
    //首页
    public function actionIndex()
    {
//        MonitorHelper::report(488672);//主页流量
        return $this->render('index');
    }

    //主要内容
    public function actionHandle($groupid, $viewid = null)
    {
        // 请求数据库
        $group = BootDB::selectOneGroup($groupid);

        // 首位 view id
        if ($viewid == null) {
            $views = $group['views'];
            if (!isset($views)) {
                return $this->render('display', ['result' => null]);
            }
            $viewid = reset($views)['view_id'];
        }

        //======数据库交互==========

        $view = View::findOne(['viewid' => $viewid]);
        //找不到视图
        if (null == $view) return $this->render('display', ['result' => null]);

        $year  = date("Y");
        $month = date("m");
        $day   = date("d");

        $file = \Yii::getAlias(\Yii::$app->params['daily_duty_file_path']) . "/$year$month$day/$viewid";
        if (!file_exists($file)) $result = null;
        else {
            //读出缓存
            $handle = fopen($file, 'r');
            $result = unserialize(fread($handle, filesize($file)));
        }

        //属性显示类型
        $session = \Yii::$app->session;

        if ($session->get('display_type'))
            $display_type = $session->get('display_type');
        else {
            $session->set('display_type', $display_type = 1);
        }

        //图片显示类型
        if ($session->get('picture_type'))
            $picture_type = $session->get('picture_type');
        else {
            $session->set('picture_type', $picture_type = 1);
        }

        //更新普通图片
        //load下视图-属性映射
        $mappingDataTool = new MappingDataTool();
        $mapping_data    = json_decode($mappingDataTool->get_mapping_data($viewid));

//        \yii\helpers\VarDumper::dump($mapping_data, 10, true);
//        die;

        //这里要注意考虑出现mapping_data为空的情况
        if ($mapping_data == null) return null;

        //向服务器请求画图
        $picture_tool = new PictureDataTool();
        if ($picture_type == 2) $picture_tool->draw_24hr_picture($viewid, $mapping_data[0]->data, $month, $day, $year);
        else if ($picture_type == 3) $picture_tool->draw_week_picture($viewid, $mapping_data[0]->data, $month, $day, $year);
        else $picture_tool->draw_compare_picture($viewid, $mapping_data[0]->data, $month, $day, $year);


//        MonitorHelper::report(488122);//进入值班内容流量

        return $this->render('display', [
            'view_id' => $viewid,
            'group_id' => $groupid,
            'group' => $group,
            'picture_type' => $picture_type,
            'display_type' => $display_type,
            'result' => $result,
        ]);
    }

    //说明
    public function actionExplain()
    {
//        MonitorHelper::report(488679);//进入说明界面流量
        return $this->render('explain');
    }

    //错误界面
    public function actionError()
    {
//        MonitorHelper::report(488673);//进入错误界面流量
        return $this->render('error');
    }

    //
    public function actionChangeType()
    {
//        MonitorHelper::report(488674);//更新显示的属性类型流量
        $session = \Yii::$app->session;
        $session->set('display_type', $_GET['type']);
        return $session->get('display_type');
    }

    //更新显示的图片类型
    public function actionPictureType()
    {
//        MonitorHelper::report(488675);//更新显示的图片类型流量
        $session = \Yii::$app->session;
        $session->set('picture_type', $_GET['picture_type']);
        return $session->get('picture_type');
    }

//添加视图
    public function actionAddView($group_id, $view_id = null, $view_name = null)
    {
        Yii::$app->session['code'] = 0;

        $bootDB = new BootDB();
        try {
            if ($bootDB->addOne2View($group_id, $view_id, $view_name) != null) {
                // 存入对应视图信息
                SaveResultTool::save_view_data($view_id, date("Y"), date("m"), date("d"));
                Yii::$app->session['code'] = 1;
                Yii::$app->session['msg'] = '视图添加成功';
            } else {
                Yii::$app->session['msg'] = '视图已存在';
            }
        } catch (\Exception $e) {
            Yii::$app->session['msg'] = $e->getMessage();
        }

        return $this->redirect(Yii::$app->homeUrl);
//        return $this->render('index', ['code' => 0, 'msg' => '属性添加成功']);
    }

    public function actionAddGroup($view_name, $mail_abbr, $mail_receiver)
    {
        Yii::$app->session['code'] = 0;
        // 改成 ajax
        if (empty($view_name)) {
            Yii::$app->session['msg'] = '请填写组名';
            return $this->redirect(Yii::$app->homeUrl);
//            return $this->render('index', ['code' => 0, 'msg' => '请填写组名']);
        }
        if (empty($mail_abbr)) {
            Yii::$app->session['msg'] = '请填写接收人简称';
            return $this->redirect(Yii::$app->homeUrl);
        }
        if (empty($mail_receiver)) {
            Yii::$app->session['msg'] = '请填写接收人';
            return $this->redirect(Yii::$app->homeUrl);
        }
        try {
            $localGroup = BootDB::addOne2Group($view_name);
        } catch (Throwable $e) {
            Yii::$app->session['msg'] = $e->getMessage();
            return $this->redirect(Yii::$app->homeUrl);
        }
        BootDB::addOne2Mail($localGroup->id, $mail_abbr, $mail_receiver);
        Yii::$app->session['code'] = 1;
        Yii::$app->session['msg'] = '新增组成功';
        return $this->redirect(Yii::$app->homeUrl);
    }
}