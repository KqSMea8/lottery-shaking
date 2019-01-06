<?php

namespace app\controllers;

use app\models\ar\ShakePhone;
use app\models\dbaccess\BootDB;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('activity_start');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionStartControl()
    {

    }

    public function actionGetUid()
    {
        $head_code =  date("m") . date("d");
        $done = false;
        $ret = ['code' => 1, 'msg' => 'get timeout'];
        $time_s = microtime();
        while (!$done) {
            $uuid = $head_code . rand(100,999);
            $done = BootDB::addOneUser($uuid);
            if (microtime() - $time_s > 10e6) { // TODO: timeout
                return $ret;
            }
        }
        $ret['code'] = 1;
        $ret['uuid'] = $uuid;
        $ret['msg'] = 'succeed';
        return json_encode($ret);
    }

    /**
     * rerender view page in ajax
     * @param $view
     * @param array $params
     * @param bool $url
     * @return string
     */
    public function actionRedirect($view, $params = [], $url = false)
    {
        if ($url) {
            return json_encode(['code' => 0, 'url' => "?r=site/redirect&view={$view}"]);
        }
        return $this->render($view, $params);
    }

    public function actionUpdateCount()
    {
        $uuid = \Yii::$app->request->post('uuid');
        $shake_count = \Yii::$app->request->post('shake_count');
        $ret = ['code' => 1, 'msg' => 'uncaught exception'];
        try {
            $update = BootDB::updateUserShakeCount($uuid, $shake_count);
            $ret['code'] = 0;
            $ret['msg'] = 'succeed';
            $ret['updated'] = $shake_count;
        } catch (\Exception $e) {
            $ret['msg'] = $e->getMessage();
        }
        return json_encode($ret);
    }
}
