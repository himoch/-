<html>
<link rel="stylesheet" href="style.css" type="text/css">
<body>
    <div class="left">
        <div class="tableInfo">
            <p class="justice">正義：</p>
            <p class="dark">邪悪：</p>
            <p class="roles">役職内訳：</p>
            <p class="lady">湖の乙女：</p>
            <p class="questOrder">クエスト順：</p>
        </div>
        <div class="myinfo">
            <p class="myname">あなたの名前：<?= $_POST['name'] ?></p>
            <p class="myside">あなたの陣営：</p>
            <p class="myrole">あなたの役職：</p>
        </div>
        <div class="memberInfo">
            <table>
                <thead>
                    <tr>
                        <td class="order"></td>
                        <td class="name">名前</td>
                        <td class="side">陣営</td>
                        <td class="selectArea"></td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="radio">
        </div>
        <div class="button">
        </div>
    </div>
    <div class="right">
        <div class="right-top">
            <table>
                <tr class="questNo">
                    <td>クエスト名</td>
                    <td>第1クエスト</td>
                    <td>第2クエスト</td>
                    <td>第3クエスト</td>
                    <td>第4クエスト</td>
                    <td>第5クエスト</td>
                </tr>
                <tr class="memberNo">
                    <td>メンバー数</td>
                    <td class="1"></td>
                    <td class="2"></td>
                    <td class="3"></td>
                    <td class="4"></td>
                    <td class="5"></td>
                </tr>
                <tr class="member">
                    <td>参加メンバー</td>
                    <td class="1"></td>
                    <td class="2"></td>
                    <td class="3"></td>
                    <td class="4"></td>
                    <td class="5"></td>
                </tr>
                <tr class="result">
                    <td>結果</td>
                    <td class="1"></td>
                    <td class="2"></td>
                    <td class="3"></td>
                    <td class="4"></td>
                    <td class="5"></td>
                </tr>
            </table>
        </div>
        <div class="right-bottom">
            <div class="message-area">
            </div>
        </div>
    </div>
</body>

<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/message.js"></script>
<script type="text/javascript" src="js/jQuery-3.3.1.min.js"></script>
<script type="text/javascript">

    var conn = new WebSocket('ws://localhost:8080?<?= http_build_query($_POST) ?>');
    conn.onopen = function(e) {
        console.log("Connection established!");
    };
    var tableInfo;
    var memberInfo;
    var questNo;
    var lady;
    var questMember;
    var track;
    var ladyTarget;
    var ladyDoer;
    var murder;

    conn.onmessage = function(e) {
        //情報を保存する
        if(JSON.parse(e.data).type != 'doAction'){
            phase = JSON.parse(e.data).phase;
            type  = JSON.parse(e.data).type;
            info  = JSON.parse(e.data).info;

            console.log('フェイズ：' + phase);
            console.log('種別：' + type);
            console.log(info);
        }
        if(doImmediate(e)){
        //ゲームを進行する
            switch(phase){
                case 0:
                //参加受付フェイズ
                    switch(type){
                        case 'prompt':
                        //入室時
                            setTableInfo(info);
                            break;
                        case 'result':
                        //役職配布
                            setMemberInfo(info);
                            break;
                    }
                    break;
                case 1:
                //メンバー決定フェイズ
                    switch(type){
                        case 'prompt':
                        //メンバー選択フォーム
                            selectMember(info);
                            break;
                         case 'result':
                             showMember(info);
                             break;
                    }
                    break;
                case 2:
                //承認フェイズ
                    switch(type){
                        case 'prompt':
                        //メンバー選択フォーム
                            approveMember(info);
                            break;
                         case 'result':
                             showVoteResult(info);
                             break;
                    }
                    break;
                case 3:
                    //クエスト実行フェイズ
                    switch(type){
                        case 'prompt':
                        //クエスト実行フォーム
                            doQuest(info);
                            break;
                         case 'result':
                             showQuestResult(info);
                             break;
                    }
                    break;
                case 4:
                    //湖の乙女フェイズ
                    switch(type){
                        case 'prompt':
                        //クエスト実行フォーム
                            doLady(info);
                            break;
                         case 'result':
                             showLadyResult(info);
                             break;
                         case 'ladyResult':
                             showLadySide(info);
                             break;
                    }
                    break;
                case 5:
                    //マーリン指名フェイズ
                    switch(type){
                        case 'prompt':
                        //クエスト実行フォーム
                            selectMerlin(info);
                            break;
                        case 'merlinResult':
                        	showMerlinResult(info);
                            break;
                         case 'result':
                        	 showMurderResult(info);
                             break;
                    }
                    break;
                case 6:
                    //最終結果表示
                    showJudge(info);
            }
        }else{
            if('<?= $_POST['messagetype'] ?>' == 'makeTable'){
                //結果表示かつ自分が円卓作成者ならゲーム進行ボタン表示
                showDoAction();
            }
        }
    };
</script>
</html>