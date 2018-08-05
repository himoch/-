<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use php\table as table;
require_once '..\php\\maangeTable.php';

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $tables;
    protected $gmList;

    public function __construct() {
        $this->tables = array();
    }

    public function __destruct(){

        unlink('../tables/tables.json');

    }

    public function onOpen(ConnectionInterface $conn) {
        $data = $this->parse_url_param($conn);

        switch($data['messagetype']){
            case 0:
                //テーブル作成
                $table = new table\table($data,$conn);
                $this->tables[$data['tableID']] = $table;

                $f = fopen('../tables/tables.json','a');
                fwrite($f, json_encode(['id' => $data['tableID'], 'name' => $data['tableName']]). "\n");
                fclose($f);
                break;
            case 1:
                //メンバー追加
                $table = $this->getTable($conn);
                if($table){
                    if($table->isLocked()){
                        $conn->send('選択した円卓は参加を締め切っています。');
                    }else{
                        $table->addPlayer($conn,$data['name']);//TODO待つ
                    }
                }else{
                    $conn->send(json_encode('円卓が存在しません'));
                }
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $table = $this->getTable($from);
        $message = json_decode($msg);
        print "受信メッセージ=====================================================\n";
        print_r($message);
        $phaseEnd = false;
        switch($message->phase){//TODO メッセージの受け取りかた
            case 1:
                //クエストメンバー一時保存
                $table->setQuestMembers($message->info);
                break;
            case 2:
                //投票
                $table->vote($from, $message->info);
                break;
            case 3:
                //クエスト実行
                $table->questAction($message->info);//待つ
                break;
            case 4:
                //湖の乙女
                $table->useLady($from,$message->info);
                break;
            case 5:
                //マーリン指名
                $table->judgeMerlin($message->info);//TODO マーリン指名メソッド
                break;
            case 99:
                //ゲーム進行
                $table->doAction();
        }
        $from->send('ok');
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $table = $this->getTable($conn);

        if($table){
            $table->detachPlayer($conn);
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function getTable($conn){
        $data = $this->parse_url_param($conn);
        return array_key_exists('tableID', $data) ? $this->tables[$data['tableID']] : false;
    }

    private function parse_url_param($conn) {
        $query = str_replace("/?", "", $conn->httpRequest->getRequestTarget());
        parse_str($query, $return_param);
        return $return_param;
    }
}