<?php
namespace php\table;

use helper;
use player;
use tablePlayers;

require_once 'player.php';
require_once 'helper.php';
require_once 'tablePlayers.php';

class table{

    public $players;
    protected $tableName;
    protected $member;
    protected $darkside;
    protected $locked;
    protected $roles;
    protected $order;
    protected $lady;
    protected $questNo;
    protected $turnNo;
    protected $questMembers;
    protected $for;
    protected $against;
    protected $questResult;
    protected $leaderNo;
    protected $quests;
    protected $phase;
    protected $votes;
    protected $lastMerlin;
    //protected $id;
/***
 * コンストラクタ
 * @param unknown $request
 * @param unknown $conn
 */
    public function __construct($request,$conn){
        $this->tableName = $request['tableName'];
        $this->member = $request['member'];
        $this->locked = false;
        $this->roles = array_key_exists('role', $request) ? $request['role'] : null;
        $this->order = $request['order'];//クエスト順のほう
        $this->lady = isset($request['lady']) ? true : false;
        $this->players = new \tablePlayers($this->lady);
        $this->addPlayer($conn,$request['name']);
        $this->darkside = helper\getConst('table.'.$this->member.'.darksides');
        $this->questNo = 1;
        $this->turnNo = 1;
        $this->for = 0;
        $this->against = 0;
        $this->questResult = array();
        $this->leaderNo = 1;
        $this->quests = [1 => '',2 => '',3 => '',4 => '',5 => '',];
        $this->phase = 0;
        $this->votes = array();
        print_r([
            'tableaName' => $this->tableName,
            'member' => $this->member,
            'roles' => $this->roles,
            'lady' => $this->lady
        ]);
    }

    private function validate($request){

    }

/**
 * フェーズ管理
 */
    public function phaseManager(){
        switch($this->phase){
            case 0://参加受付
                //→メンバー選択
                //リーダーは〇〇さんです。
                //クエストメンバーを選択してください。
                $this->phase = 1;
                $this->players->sendPromptMessage($this->phase, $this->getMemberSelectInfo());
                break;
            case 1://メンバー選択
                //→承認フェイズ
                //クエストメンバーを承認するか却下するか選んでください。
                $this->phase = 2;
                $this->players->sendPromptMessage($this->phase);
                break;
            case 2://承認フェイズ
                $this->nextLeader();
                if($this->fiveDenied()){
                    //→ゲーム終了
                    //邪悪陣営の勝利です
                    $this->players->sendLastMessage('dark');
                }elseif($this->isApproved()){
                    //→クエスト実行フェイズ
                    //〇〇さんは、クエストを実行してください
                    $this->players->setQuestMembers($this->questMembers);
                    $this->phase = 3;
                    $this->resetTurnNo();
                    $this->players->sendPromptMessage($this->phase);
                }else{
                    //→メンバー選択フェイズ
                    //リーダーは〇〇さんです。
                    //クエストメンバーを選択してください。
                    $this->phase = 1;
                    $this->setTurnNo();
                    $this->players->sendPromptMessage($this->phase, $this->getMemberSelectInfo());
                }
                $this->resetVote();
                break;
            case 3://クエスト実行フェイズ
                if($this->judge() == 'justice'){
                    //→ゲーム終了
                    //正義陣営の勝利です
                    if(in_array('Merlin',$this->roles)){
                        //→マーリン指名フェイズ
                        //しかし邪悪陣営に逆転のチャンス！
                        //邪悪陣営のみなさんは相談してマーリンを指名してください。
                        $this->phase  = 5;
                        $this->players->sendPromptMessage($this->phase);
                    }else{
                        $this->players->sendLastMessage('justice');
                    }
                }elseif($this->judge() == 'dark'){
                    //→ゲーム終了
                    //邪悪陣営の勝利です
                    $this->players->sendLastMessage('dark');
                }elseif($this->doLadyPhase()){
                    //→湖の乙女フェイズ
                    //〇〇さんは、陣営を見たいプレイヤーを選んでください。
                    $this->phase = 4;
                    $this->endQuest();
                    $this->players->sendPromptMessage($this->phase);
                }else{
                    //→メンバー選択フェイズ
                    //リーダーは〇〇さんです。
                    //クエストメンバーを選択してください。
                    $this->phase = 1;
                    $this->endQuest();
                    $this->players->sendPromptMessage($this->phase, $this->getMemberSelectInfo());
                }
                break;
            case 4://湖の乙女フェイズ
                //→クエスト実行フェイズ
                //リーダーは〇〇さんです。
                //クエストメンバーを選択してください。
                $this->phase = 1;
                $this->players->sendPromptMessage($this->phase, $this->getMemberSelectInfo());
                break;
            case 5://マーリン指名フェイズ
                if($this->players->judgeMerlin($this->lastMerlin)){
                    //邪悪陣営の逆転勝利
                    $this->players->sendLastMessage('dark');
                }else{
                    //正義陣営の勝利
                    $this->players->sendLastMessage('justice');
                }
                //→ゲーム終了
                //TODO 終わり方
                break;
        }
    }

/**
 *ロック中かどうか判定
 */
    public function isLocked(){
        return $this->locked;
    }

/***
 * プレイヤーを送信対象者に加える
 * @param unknown $conn
 */
    public function addPlayer($conn,$name){
        $this->players->attach($conn,new \player\player($name));
        $this->players->sendEnterMessage($conn,$this->makeTableInfo(),$name);//TODO 〇〇さんが入室しました
        if($this->maxPlayer()){
            //全員揃ったらロックして役職配布
            $this->locked = true;
            $this->players->deal($this->getRoles(),$this->lady);
            return true;
        }
        return false;
    }

/***
 * プレイヤーを送信対象者から外す
 * @param unknown $conn
 */
    public function detachPlayer($conn){
        $this->players->detach($conn);
    }

/***
 * クエストメンバーに指名されたメンバーを保存する
 * @param unknown $members
 */
    public function setQuestMembers($members){
        $this->questMembers = $members;
        $this->players->sendResultMessage($this->phase, $this->questMembers);
        return true;
        //TODO 〇〇さんが選択されました。
    }

/***
 * 投票を設定する
 * @param unknown $type
 */
    public function vote($from, $type){

        $this->votes[$this->players[$from]->getOrder()] = $type;

        if($type == 'for'){
            $this->for += 1;
        }else{
          $this->against +=1;
        }
        if($this->voteFull()){
            //全員分集まったら結果送信
            $this->players->sendResultMessage($this->phase, [$this->isApproved(), $this->votes]);
            return true;
        }
        return false;
    }

/***
 * クエストの成否カードを設定する
 * @param unknown $result
 */
    public function questAction($result){
        //クエストカード設定
        $this->questResult[] = $result; //TODO string success / failed
        if($this->questActionFull()){
            //全員分揃ったら成否を設定しメッセージ送信
            $this->setQuestResult($this->successed());
            $this->players->sendResultMessage($this->phase, [$this->successed(),array_count_values($this->questResult)]);
            return true;
        }
        return false;
    }

/***
 * 湖の乙女を実行する
 * @param unknown $conn 使用者のコネクション
 * @param unknown $name 使用対象者名
 * @return unknown
 */
    public function useLady($conn,$order){
        $this->players[$conn]->robLady();
        $this->players->sendLadyResult($conn,$order,$this->phase);
        return true;
    }

/***
 * 指名されたマーリンを送信する
 * @param unknown $conn 使用者のコネクション
 * @param unknown $name 使用対象者名
 * @return unknown
 */
    public function judgeMerlin($name){
        $this->players->sendMerlinResult($this->phase, $name);
        $this->lastMerlin = $name;
        return true;
    }
/**
 * ゲーム進行ボタンを表示する
 */
    public function doAction(){
        $this->players->doAction();
        $this->phaseManager();
    }


/***
 * 円卓の陣営と役職を決定する
 * @return array
 */
    private function getRoles(){
        $justiceRoles = helper\getConst('justiceRoles');
        $justice = array();
        $dark = array();

        //正義配列と邪悪配列を作る
        if($this->roles){
            foreach($this->roles as $role){
                if(in_array($role,$justiceRoles)){
                    $justice[] = ['side' => 'justice', 'role' => $role];
                }else{
                    $dark[] = ['side' => 'dark','role' => $role];
                }
            }
            $dark[] = ['side' => 'dark', 'role' => 'murder'];
        }

        //役職持ち以外を埋める
        $justice = array_pad($justice, $this->member - $this->darkside, ['side' => 'justice', 'role' => '']);
        $dark = array_pad($dark, $this->darkside, ['side' => 'dark','role' => '']);

        //合体して役職配列作成
        $roles = array_merge($justice,$dark);
        shuffle($roles);
        print_r($roles);

        return $roles;
    }

/**
 * プレイヤーが全員そろったか判定する
 */
    private function maxPlayer(){
        return ($this->players->count() == $this->member);
    }

//TODO 湖の乙女送信メソッド作る
/***
 * 湖の乙女を実行するか判定する
 * @return boolean|unknown
 */
    private function doLadyPhase(){
        if($this->questNo >= 2){
            return $this->lady;
        }else{
            return false;
        }
    }

/**
 * リーダーの順番を回す
 */
    private function nextLeader(){
        if($this->leaderNo == $this->member){
            $this->leaderNo = 1;
        }else{
            $this->leaderNo += 1;
        }
    }

/***
 * 全員分の投票が出そろったか判定する
 * @return boolean
 */
    private function voteFull(){
        if(count($this->votes) == $this->member){
            return true;
        }else{
            return false;
        }
    }
/***
 * 承認されたか判定する
 * @return boolean
 */
    private function isApproved(){
        return ($this->for > $this->against);
    }
/***
 * クエストの成否カードがすべて集まったか判定する
 * @return boolean
 */
    private function questActionFull(){
        return (count($this->questResult) == count($this->questMembers));
    }
/***
 * クエストの成否を判定する
 * @return boolean
 */
    private function successed(){
        if($this->questNo == 4 && \helper\getConst("table.{$this->member}.twoFails")){
            $result = array_count_values($this->questResult);
            return (isset($result['failed']) && $result['failed'] < 2);
        }else{
            return !in_array('failed', $this->questResult);
        }
    }
/***
 * クエスト終了処理
 */
    private function endQuest(){
        //クエスト番号を一つすすめる
        $this->questNo += 1;

        //クエストメンバーを初期化する
        $this->questMembers = null;
        $this->players->resetQuestMembers();

        //クエスト結果を初期化する
        $this->questResult = array();

    }
/***
 * 投票トラックを進める
 */
    private function setTurnNo(){
        $this->turnNo += 1;
    }
/***
 * 投票トラックを初期化する
 */
    private function resetTurnNo(){
        $this->turnNo = 1;
    }
/***
 * 現クエストの成否を設定する
 * @param unknown $result
 */
    private function setQuestResult($result){
        $this->quests[$this->questNo] = $result ? 'success' : 'failed';
    }
/**
 * 投票リセット
 */
    private function resetVote(){
        $this->tempMembers = null;
        $this->for = 0;
        $this->against = 0;
        $this->votes = array();
    }

/***
 * 勝敗を判定する
 * 正義が勝ち:justice
 * 邪悪が勝ち:dark
 * 勝敗まだ:false
 * @return string|boolean
 */
    private function judge($last = false){
        if($this->questNo < 3){
            return false;
        }

        $result = array_count_values($this->quests);
        if(isset($result['success']) && $result['success'] == 3){
            return 'justice';
        }elseif($result['failed'] == 3){
            return 'dark';
        }else{
            return false;
        }
    }

    private function fiveDenied(){
        if($this->turnNo == 5 && !$this->isApproved()){
            return true;
        }else{
            return false;
        }
    }

    private function resetGame(){

    }

    private function makeTableInfo(){
        return [
            'members' => $this->member,
            'contents' => helper\getConst("table.{$this->member}"),
            'roles' => $this->roles,
            'lady' => $this->lady,
            'questOrder' => (boolean) $this->order
        ];
    }

/**
 * 現在クエストの参加人数を取得する
 *
 */
    private function getCurrentQuestMember(){
        $serchQuestNo = $this->questNo - 1;
        $questMembers = \helper\getConst("table.{$this->member}.questMember.{$serchQuestNo}");
        return $questMembers;
    }

    /**
     * メンバー選択フェイズの送信内容を取得する
     */
    private function getMemberSelectInfo(){
        return  [
            'leaderNo'=>$this->leaderNo,
            'questNo'=>$this->questNo,
            'turnNo'=>$this->turnNo,
        ];
    }
}