<?php
/**
 * Created by PhpStorm.
 * User: chewchen
 * Date: 2018/12/11
 * Time: 11:27
 */

namespace app\models\ar;

use Exception;
use Throwable;
use yii\base\InvalidConfigException;
use \yii\db\ActiveRecord;
use \Yii;
use yii\db\StaleObjectException;

class LocalView extends ActiveRecord
{
    public static function getDb() {
        try {
            return Yii::$app->get('bootdb');
        } catch (InvalidConfigException $e) {
        }
        return null;
    }

    public static function tableName() {
        return 'local_view';
    }

    /**
     ** 往数据表添加一个 view
     * @param $group string | int 视图所属的组名 或 group id
     * @param $view_id int
     * @param $view_name string
     * @throws Exception group id 不存在则抛出异常
     * @return LocalView|null
     * @throws Throwable
     */
    public static function addOne2View($group, $view_id, $view_name)
    {
        if (empty($view_id)) {
            throw new Exception("请至少输入视图 ID");
        }

        $cloudView = View::findOne(['viewid' => $view_id]);
        // monitor 无此视图
        if ($cloudView == null) {
            throw new Exception("Illegal View:   Check the view ID .");
        }

        if (empty($view_name)) {
            $view_name = $cloudView['viewname'];
        }
        // 重复的不添加
        if (LocalView::findOne(['view_id' => $view_id]) != null ||
            LocalView::findOne(['view_name' => $view_name]) != null){
            return null;
        }

        $self = new self();
        //  判断是否是 id
        $is_group_id = is_numeric($group);
        if ($is_group_id) {
            if (LocalGroup::findOne($group) == null) {
                throw new Exception("'local_group' database does not exists id=$group");
            }
            $self->group_id = $group;
        }
        else {
            $self->group_id = LocalGroup::addOne2Group($group)['id'];
        }

        $self->view_name = $view_name;
        $self->view_id = $view_id;
        $self->save();
        return $self;
    }

    /**
     * 删除某一个 view
     * @param $view int|string viewid 或 viewname
     */
    public static function deleteOneView($view)
    {
        $is_view_id = is_numeric($view);
        if ($is_view_id)
            $key = 'view_id';
        else
            $key = 'view_name';
        try {
            LocalView::findOne([$key => $view])->delete();
        } catch (StaleObjectException $e) {
            echo $e->getMessage();
        } catch (Throwable $e) {
        }
    }

}
