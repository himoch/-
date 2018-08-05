<?php
// use helper;

require_once 'helper.php';

class tablePlayers extends SplObjectStorage{

    protected $ladyDo;
    protected $serchArray;

    public function __construct($lady){
        $this->ladyDo = $lady;
    }

    public function attach($object,$inf = null){
        parent::attach($object,$inf);
        $this->serchArray[] = $inf;
    }

/**
 * 入室時メッセージを送信する
 */
    public function sendEnterMessage($from, $tableInfo, $name){

        foreach($this as $conn){
            $message = array();
            $message['phase'] = 0;
            $message['type'] = 'prompt';

            if($conn == $from){
                //入室者にはテーブルの設定を送信する
                $message['info'] = $tableInfo;
            }else{
                //入室者以外には入室者の名前を送信
                $message['info'] = $name;
            }
            $conn->send(json_encode($message));
        }
    }

/**
 * 全員にプロンプトメッセージを送信する
 */
    public function sendPromptMessage($phase, $info = ''){

        print "プロンプトメッセージ=====================================================\n";

        $message = array();
        $message['phase'] = $phase;
        $message['type'] = 'prompt';
        $message['info'] = '';

        foreach($this as $conn){
            $player = $this[$conn];
            $message['info'] = $this->getSendInfo($phase, $player, $info);
            print_r($message);

            $conn->send(json_encode($message));
        }
    }

/**
 * 全員に結果メッセージを送信する
 */
    public function sendResultMessage($phase,$result){
        print "結果メッセージ=====================================================\n";

        $message = array();
        $message['phase'] = $phase;
        $message['type'] = 'result';
        $message['info'] = $result;
        print_r($message);

        foreach($this as $conn){
            $conn->send(json_encode($message));
        }
    }

/**
 * 全員に最終結果メッセージを送信する
 */
    public function sendLastMessage($side){
        print "最終結果メッセージ=====================================================\n";

        $message = array();
        $message['phase'] = 6;
        $message['type'] = 'judge';
        $message['info'] = ['winner' => $side, 'table' => $this->getContents()];
        print_r($message);

        foreach($this as $conn){
            $conn->send(json_encode($message));
        }
    }

/**
 * 湖の乙女の結果を送信する
 */
    public function sendLadyResult($conn, $userId, $phase){

        $side = $this->getSide($userId);

        $message = [
            'phase' => $phase,
            'type' =>'ladyResult',
            'info' => $side
        ];
        $conn->send(json_encode($message));
        //全員に選択された人を送信
        $this->sendResultMessage(4,$userId);
    }

    /**
     * マーリン指名の結果を送信する
     */
    public function sendMerlinResult($phase,$name){

        //全員に選択された人を送信
        $message = [
            'phase' => $phase,
            'type' =>'merlinResult',
            'info' => $name
        ];
        foreach($this as $conn){
            $conn->send(json_encode($message));
        }
        $this->sendResultMessage($phase,[
            'murdered'=>$name,
            'isMerlin'=>$this->judgeMerlin($name)
        ]);
    }

/**
 * マーリン指名の結果を取得する
 */
    public function judgeMerlin($merlin){
        foreach($this->serchArray as $player){
            if($player->getOrder() == $merlin){
                return $player->isMerlin();
            }
        }
    }

/***
 * 役職をプレイヤーに設定し、役職を送信する
 */
    public function deal($roles){
        $i = 0;

        $order = array();

        shuffle($this->serchArray);
        foreach($this->serchArray as $player){
            $player->giveSide($roles[$i]['side']);
            $player->giveRole($roles[$i]['role']);
            $player->setOrder($i + 1);

            $order[$i + 1] = $player->getName();

            if($this->ladyDo && $i + 1 == $this->count()){
                //湖の乙女が有効ならば、順番が最後のプレイヤーに湖の乙女を渡す
                $player->giveLady();
            }
            $i++;
        }
        $this->sendRoles($order);
    }

/***
 * クエストメンバーを設定する
 */
    public function setQuestMembers($questMembers){
        foreach($this as $conn){
            if(in_array($this[$conn]->getOrder(), $questMembers)){
                $this[$conn]->setQuestMember();
            }
        }
    }

/***
 * クエストメンバーをリセットする
 */
    public function resetQuestMembers(){
        foreach($this as $conn){
            $this[$conn]->resetQuestMember();
        }
    }

/***
 * ゲームを進行する
 */
    public function doAction(){
        foreach($this as $conn){
            $conn->send(json_encode(['phase'=>99,'type'=>'doAction','info'=>null]));
        }
    }

/**
 * 湖の乙女で指定されたメンバーの陣営を取得する
 * @param unknown $name
 */
    private function getSide($id){
        foreach($this->serchArray as $player){
            if($player->getOrder() == $id){
                return $player->ladyCheck();
            }
        }
    }

/***
 * 全員に役職を配布する
 */
    private function sendRoles($order){

        $this->rewind();
        foreach($this as $conn){
            $player = $this[$conn];

            $message = array();

            $side = $player->getSide();
            $role = $player->getRole();
            $others = '';

            switch ($player->getRole(true)){
                case 'Merlin':
                    $others = $this->getDarkSide(true);
                    break;
                case 'Perceval':
                    $others = $this->getMerlin();
                    break;
                case 'justice':
                case 'Oberon':
                    $others = NULL;
                    break;
                case 'Mordred':
                case 'Morgan':
                case 'murder':
                case 'dark':
                    $others = $this->getDarkSide(false);
                    break;
            }

            $message['phase'] = 0;
            $message['type'] = 'result';
            $message['info'] = [
                'side' => $side,
                'role' => $role,
                'haveLady' => $player->hasLady(),
                'others' => $others,
                'players' => $order
            ];

            print_r($player);

            $conn->send(json_encode($message));
        }

    }

/***
 * マーリン（とモルガナ）のプレイヤーを取得する
 * @return NULL[]
 */
    private function getMerlin(){
        $result = array();

        foreach($this->serchArray as $player){
            if($player->isMerlin()){
                $result[] = $player->getOrder();
            }
        }
        return $result;
    }

/***
 * 邪悪陣営のプレイヤーを取得する
 * @param unknown $isMerlin
 * @return NULL[]
 */
    private function getDarkSide($isMerlin){
        $result = array();

        foreach($this->serchArray as $player){
            if($player->isDark($isMerlin)){
                $result[] = $player->getOrder();
            }
        }
        return $result;
    }

/***
 * 湖の乙女でチェックできるメンバーを取得する
 */
    private function getToBeChecked(){
        $result = array();

        foreach($this->serchArray as $player){
            if(!$player->checkedByLady() && !$player->hasLady()){
                $result[] = $player->getOrder();
            }
        }
        return $result;
    }

    /***
     * 暗殺者を取得する
     */
    private function getMurder(){
        $result = array();

        foreach($this->serchArray as $player){
            if($player->isMurder()){
                return $player->getOrder();
            }
        }
    }

    /**
     * マーリン候補取得
     */
    private function getJustice(){
        $result = array();
        foreach($this->serchArray as $player){
            if(!$player->isDark(false)){
                $result[] = $player->getOrder();
            }
        }
        return $result;
    }


    /***
     * 送信する情報を取得する
     */

    private function getSendInfo($phase, $player, $tableinfo){
        switch($phase){
            case 0://役職配布
                break;
            case 1://メンバー選択
                $info = [
                    'leader'=>$player->isLeader($tableinfo['leaderNo']),
                    'leaderNo'=>$tableinfo['leaderNo'],
                    'questno'=>$tableinfo['questNo'],
                    'turnNo' =>$tableinfo['turnNo'],
                ];
                break;
            case 2://承認
                $info = '';
                break;
            case 3://クエスト実行
                $info = $player->isQuestMember();
                break;
            case 4://湖の乙女
                $info = [
                    'execute' => $player->hasLady(),
                    'list' => $this->getToBeChecked()
                ];
                break;
            case 5://マーリン指名
                $info = [
                    'isMurder' => $player->isMurder(),
                    'murder' => $this->getMurder(),
                    'justice' => $this->getJustice(),
                ];
                break;
        }
        return $info;
    }

/**
 * 内訳を取得する
 */
    private function getContents(){
        $result = array();
        foreach($this->serchArray as $player){
            $result[$player->getOrder()] = ['side' => $player->getSide(), 'role' => $player->getRole()];
        }
        return $result;
    }
}