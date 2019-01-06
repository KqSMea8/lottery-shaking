<?php

namespace app\controllers;


use app\models\dbaccess\BootDB;
use app\models\handler\Process;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Controller;

class MailController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionActiveSend()
    {
        require "../models/tools/simple_html_dom.php";
        return 0;
        //处理content
        $html = str_get_html($_POST['content']);
        $e    = $html->find('.none');
        foreach ($e as $item) {
            $item->innertext = '';
        }

        //uniqid() 函数基于以微秒计的当前时间，生成一个唯一的 ID.
        $file_name = md5(uniqid()) . '.txt';

        //线上存放mail目录
        $file_path = \Yii::getAlias(\Yii::$app->params['mail_file_path']);
        if (!file_exists($file_path))
            mkdir($file_path, 0777, true);

        $myFile = fopen($file_path . $file_name, "w") or die($retMsg = "内部错误, 邮件读取失败");
        $txt = $html;
        fwrite($myFile, $txt);
        fclose($myFile);

        $web_group = BootDB::selectOneMail($_POST['group_id']);
        $receiver = $web_group['receiver'];
        $groupAbbr = $web_group['abbr'];

        //执行python发送邮件
        $shellPath = \Yii::getAlias(\Yii::$app->params['shell_path']) . "actionSend.py";


//        MonitorHelper::report(488676);//发送邮件数量
        //        exec("python " . $shellPath .
//            ' "' . $_POST['sender'] . '" ' .
//            ' "' . $groupAbbr . '" ' .
//            ' "' . $receiver . '" '  .
//            '"' . $_POST['subject'] . '" ' .
//            '"' . $file_path . '"' .
//            " >> " . $logFilePath . " 2>&1 &", $out, $ret);


        $cmd = "python ../shell/actionSend.py chew@futunn.com chewchen@futunn.com chewchen@futunn.com title content";
        $process = new Process($cmd);
        $cache = Yii::$app->cache;
        // set cache for pid, before blocking
        $cache->set('mail_process_pid', $process->getPid());

        $retMsg = null;
        // error

//        $p = '/\[Errno.+]\s*(.*)/';

        $errMsg = $process->waitForErrMsg();

        // shell print msg
        $stdout = $process->waitForMsg();

        // log
        $logPath   = \Yii::getAlias(\Yii::$app->params['send_email_log_path']);
        if (!file_exists($logPath))
            mkdir($logPath, 0777, true);
        $logFilePath = \Yii::getAlias(\Yii::$app->params['send_email_log_path']) . "send_email_log.txt";
        $logFile = fopen($logFilePath, "w") or die($retMsg = '系统内部错误，日志读取失败');
        fwrite($logFile, $stdout);
        fwrite($logFile, $errMsg);
        fclose($logFile);

        $process->waitForDestroy();

        if (!empty($errMsg)) $retMsg = $errMsg;
        return $retMsg === null ? 0 : $retMsg;
//        return json_encode(array(
//            'code' => 0,
//            'pid' => $process->getPid(),
//        ));
    }

    public function actionReleaseMailSender($pid)
    {
        Process::promptDestroy($pid);
        return $pid;
    }

    /**
     * ask for pid as soon, so use polling way
     * @return mixed|string pid or error code
     */
    public function actionGetMailSender()
    {
        $pid = Yii::$app->cache->get('mail_process_pid');
        if ($pid === false)
            return -1;
        Yii::$app->cache->delete('mail_process_pid');
        return $pid;
    }


    //填写发件人
    public function actionGetSender()
    {
        return $this->render('sendMail', ['group_id' => $_GET['group_id'], 'subject' => $_GET['subject']]);
    }


    //编辑邮件
    public function actionEditMail()
    {
        $cc = 'glame(陈国林) <glame@futunn.com>; g_tech_web(研发部-web研发中心) <g_tech_web@futunn.com>';
//        new EmailArr();
//        $web_group = EmailArr::$tech_web_group['info'][$_GET['group_id']];
        $web_group = BootDB::selectOneMail($_GET['group_id']);
        $subject   = "【{$_GET['duty_name']}值班】" . '  ' . date('Y') . '/' . date('m') . '/' . date('d');
        return $this->render('editMail', ['receiver' => $web_group->receiver, 'cc' => $cc, 'subject' => $subject, 'group_id' => $_GET['group_id']]);
    }


    //保存邮件请求
    public function actionAddToMail()
    {
        return $this->render('addToMail');
    }


    public function actionSave()
    {
        require "../models/tools/simple_html_dom.php";

        //获得邮件内容
        $content = $_POST['content'];
        //如果邮件内容为空或者需要重写，则生成邮件框架
        if ($content == null || $_POST['overwrite']) {
            $content = null;


            //获取文件位置
//            $file = \Yii::getAlias(\Yii::$app->params['duty_info_path']) . "view_set";
//            //解析json
//            $handle = fopen($file, 'r');
//
//            $dutyArr = json_decode(fread($handle, filesize($file)), true);
//
//            $view_set = $dutyArr[$_POST['group']];
            $group = BootDB::selectOneGroup($_POST['group']);

            //拼装html
            //！这里把id复制为group的值加一是为了防止出现等下查找出现id为0的情况（因为工具使用上）
            $content = "<html>"."<h3 id='" . ++$_POST['group'] . "'>【{$group['group_name']}值班】" . '  ' . date('Y') . '/' . date('m') . '/' . date('d') . '  ' . '  http://www.zhiban.com</h3>';
            foreach ($group['views'] as $view) {
                $content = $content . "<div  style='display: none' class='none' id = \"{$view['view_id']}\" ><div><strong><a href='http://monitor.server.com/link/graph/viewid:{$view['view_id']}'>{$view['view_name']}</a></strong><p id='none'>无明显异常</p></div><br><br></div>";
            }

            $content = $content . "</html>";
        } else {

            $html = str_get_html($content);

            //查看是否有其他值班内容
            //！！此工具不能查找#0，所以上面加了一
            $e = $html->find('#' . ++$_POST['group'], 0);

            //如果不是当前值班，返回失败
            if ($e == null) return false;

        }

        //保存图片生成地址
        $url = "http://172.28.12.3/graph/ViewGif/ViewGif_{$_POST['view']}/{$_POST['year']}_{$_POST['month']}/{$_POST['view']}_{$_POST['attr']}_{$_POST['year']}{$_POST['month'] }{$_POST['day']}.gif";
        $dir = \Yii::getAlias(\Yii::$app->params['monitor_download_pic_path']);
        if (!file_exists($dir))
            mkdir($dir, 0777, true);

        $img = "{$_POST['view']}_{$_POST['attr']}_" . date("h_i_s") . ".gif"; //保存到本地的name,可以是保存地址+图片名称
        file_put_contents($dir . '/' . $img, file_get_contents($url));

        $img_url = 'http://www.zhiban.com/monitor_pic/' . $img;

        //html解析
        $html = str_get_html($content);
        //找到对应的视图
        $e        = $html->find('#' . $_POST['view'], 0);
        $e->style = 'display: inline';
        $e->class = 'existence';
        $e        = $e->find('div', 0);
        //删除视图下无异常的说明
        $n = $e->find('#none', 0);
        if ($n != null) $n->outertext = ''; // Returns: " div"
        //添加异常
        $e->innertext = $e->innertext . "<br><div>- {$_POST['attr']} {$_POST['attr_name']}【{$_POST['dir_name']}】 &nbsp;&nbsp; <b>{$_POST['explain']}</b> </div><img src=\"$img_url \"><br>";
        $doc          = $html;
        //返回异常
        return $doc;

    }

}