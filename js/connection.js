
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
            if($(':hidden').val() == 'makeTable'){
                //結果表示かつ自分が円卓作成者ならゲーム進行ボタン表示
                showDoAction();
            }
        }
    };