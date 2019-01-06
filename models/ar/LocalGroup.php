<?php
/**
 * Created by PhpStorm.
 * User: chewchen
 * Date: 2018/12/11
 * Time: 14:39
 */

namespace app\models\ar;

use Exception;
use Throwable;
use yii\base\InvalidConfigException;
use \yii\db\ActiveRecord;
use \Yii;
use yii\db\StaleObjectException;

class LocalGroup extends ActiveRecord{

    /**
     * rewrite inherit
     * @return null|object|\yii\db\Connection
     */
    public static function getDb() {
        try {
            return Yii::$app->get('bootdb');
        } catch (InvalidConfigException $e) {
        }
        return null;
    }

    /**
     * inherit
     * rewrite
     * @return string
     */
    public static function tableName() {
        return 'local_group';
    }


    /**
     * 往数据表 local_group 添加一个 group, 重复则不添加
     * @param $group_name
     * @return self
     * @throws Throwable
     */
    public static function addOne2Group($group_name){
        $self = new self();
        $select = LocalGroup::find()->where(['group_name' => $group_name])->one();
        if (isset($select)) { //已存在此 group
            $self->id = $select['id'];
            $self->group_name = $select['group_name'];
            throw new Exception("$group_name 已存在");
        }
        $self->group_name = $group_name;
        $self->save();

        return $self;
    }

    /**
     * 删除 local_group 中的一行数据
     * @param $group int|string group id 或 group name
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function deleteOneGroup($group){
        $is_group_id = is_numeric($group_id = $group);
        if (!$is_group_id){
            $select = LocalGroup::findOne(['group_name' => $group]);
            if (!isset($select))
                return;
            $group_id = $select['id'];
        }

        if (LocalView::findOne(['group_id' => $group_id]) != null){
            throw new Exception('无法删除 group ' . $group_id . '， local_view 中还存在组员.');
        }
        LocalGroup::findOne($group_id)->delete();
        echo '删除成功.';
    }

    public function getViews()
    {
        return $this->hasMany(LocalView::className(), ['group_id' => 'id'])->all();
    }


}