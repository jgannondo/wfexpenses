<?php


class ApiController extends QueriesController
{


    const API_KEY_NAME = "pdmkey";
    const INCOME_EXPENSES_ACTION_TAG = "ie";
    const USER_CREDENTIALS_ACTION_TAG = "uc";

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getApiUser(){
        $key = isset($_GET[self::API_KEY_NAME]) ? $_GET[self::API_KEY_NAME] : null;
        if($key != null ){
            $setting = Settings::model()->findByAttributes(
                ['setting_name'=>'api_key','setting_value'=>$key]);
            if($setting !== null ){
                $userId = $setting->user_id;
                $user = Users::model()->findByPk($userId);
                return $user;
            }
        }

        return null;
    }

    public function beforeAction($action)
    {
        $user = $this->getApiUser();
        if($user){
            $userid = new ApiUserIdentity($user->username,$user->email);
            $userid->authenticate();
            Yii::app()->user->login($userid,0);
            Yii::app()->session['userid'] = Yii::app()->user->userid;
            return parent::beforeAction($action); // TODO: Change the autogenerated stub
        }else{
            Utils::jsonResponse('bad','Authentication failed','');
        }
    }

    public function actionGet(){
        $action = $_GET['action'];
        if($action == self::INCOME_EXPENSES_ACTION_TAG){
            $this->appGetIEThisMonth();
        }else{
            Utils::jsonResponse('bad','Bad Request Not found');
        }
    }


    public function actionPost(){
        $action = $_GET['action'];
        if($action == 'create_transaction'){
            $this->appCreateTransaction();
        }else if($action == self::USER_CREDENTIALS_ACTION_TAG){
            $this->getCredentials();
        }else{
            Utils::jsonResponse('bad','Bad Request Not found');
        }
    }

    public function appGetIEThisMonth(){
        $data = [
            'income' => $this->getIncomeThisMonth(),
            'expenses' => $this->getExpenses(),
            'worth' => $this->getNetWorth(),
            'savings' => $this->getSavings()
        ];
        Utils::jsonResponse('good','good',$data);
    }

    public function appGetExpenseThisMonth(){

    }

    public function appGetIncomeThisMonth(){

    }

    public function appCreateTransaction(){

    }

    public function getCredentials(){
        $email = Utils::getPost("email");
        $password = Utils::getPost("password");
        $record = Users::model()->findByAttributes(array('email'=>$email));
        if(empty($email) || empty($password)){
            Utils::jsonResponse('bad','No user or email found');
        }else if($record == null){
            Utils::jsonResponse('bad','No api key is setup for this user');
        }else{
            $apiKey = Utils::getUserSetting('api_key', $record->id,"");
            $ph=new PasswordHash(Yii::app()->params['phpass']['iteration_count_log2'],
                Yii::app()->params['phpass']['portable_hashes']);
            if(!$ph->CheckPassword($password, $record->password)){
                Utils::jsonResponse('bad','User name or password incorrect');
            }else if(empty($apiKey)){
                Utils::jsonResponse('bad','No api key is setup for this user');
            }else{
                Utils::jsonResponse('good','good', ['apiKey'=>$apiKey,'username'=>$record->username]);
            }
        }
    }


}
