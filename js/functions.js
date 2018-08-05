//テーブル情報設定
function setTableInfo(info){
    if(typeof info.contents === "undefined"){
    	//入室メッセージ表示
    	showMessage([phase,type,1],{name:info});
        return;
    }

    tableInfo = info.contents;
    lady = info.lady;

    var justice = (info.members - info.contents.darksides);
    var dark    = info.contents.darksides;
    //役職名取得
    roles = [];
    for(var key in info.roles){
    	roles.push(getRoleName(info.roles[key]));
    }
    var questOrder = info.questOrder;

    $('.tableInfo .justice').append(justice);
    $('.tableInfo .dark').append(dark);
    if(roles){
        $('.tableInfo p.roles').append($('<div>' + roles.join('<br>') + '</div>'));
    }
    $('.tableInfo .lady').append(lady ? 'あり' :  'なし');
    $('.tableInfo .questOrder').append(questOrder ? '自由選択' : '順番通り');
    if(lady){
        $('.left table thead td.side').after($('<td class="lady">湖の<br>乙女</td>'));
    }

    var questMember = info.contents.questmember;
    var twoFails    = info.contents.twoFails;

    var i = 1;
    questMember.forEach(function(value){
        $('.right-top .memberNo .' + i).append(value);
        i++
    })

    if(twoFails){
        $('.right-top .memberNo .4').append('<br>（２枚）')
    }
	showMessage([phase,type,2]);
}

//メンバー情報設定
function setMemberInfo(info){
    memberInfo = info;

    var side     = getSideName(info.side);
    var role     = getRoleName(info.role);
    var haveLady = info.haveLady;
    var others   = info.others;
    var players  = info.players;

    //TODO確認モーダル出す

    $('.myinfo .myside').append($('<span class="' + info.side + '">' + side + '</span>'));
    $('.myinfo .myrole').append($('<span class="' + info.side + '">' + role + '</span>'));

    if(info.side == 'dark' || info.role == 'Merlin'){
        var otherName = getSideName('dark');
        var otherClass = 'dark';
    }else if(info.role == 'Perceval'){
        var otherName = getRoleName('Merlin');;
        var otherClass = 'Merlin';
    }else{
        var otherName = '';
        var otherClass = '';
    }

    for(var order in players){
        var sideName = '';
        var ladyHave = '';

        var serchKey = Number(order);
        if($.inArray(serchKey, others) != -1){
            sideName = otherName;
        }

        if(lady && order == Object.keys(players).length){
            ladyHave = '●';
            ladyDoer = order;
        }
        var orderTD = $('<td class="order">' + order + '</td>');
        var nameTD  = $('<td class="name">' + players[order] + '</td>');
        var sideTD  = $('<td class="side ' + otherClass + '">' + sideName + '</td>');
        var selectArea  = $('<td class="selectArea"></td>');
        var ladyTD = lady ? $('<td class="lady">' + ladyHave + '</td>') : '';

        var row = $('<tr class="' + order + '">').append(orderTD).append(nameTD).append(sideTD).append(ladyTD).append(selectArea);

        $('.memberInfo tbody').append(row);
        $('.left table').fadeIn(500);
    }
	var no = role != 'なし' ? 2 :3;
	//役職・陣営メッセージ表示
	var className = info.side == 'justice' ? 'success' : 'failed';
    showMessage([phase,type,1],{'side':side,'class':className});
    showMessage([phase,type,no],{'role':role,'class':className});
}

//クエストメンバー選出
function selectMember(info){
    questNo = info.questno;
    leaderNo = info.leaderNo;

	$('.memberInfo td.name').removeClass('selected');

    $('td.order').removeClass('current');
    $('tr.' + leaderNo + ' td.order').addClass('current');


    //リーダーメッセージ表示
    showMessage([phase,type,1],{'name':memberInfo.players[info.leaderNo],'no':info.turnNo});
    if(info.leader){
        //リーダーならフォームを表示
        $('.left table tbody td.selectArea').each(function(no,input){
            $(input).append($('<input type="checkbox" value="' + (no + 1) + '">'));
        })
        $('.left .button').append($('<button type="button" onclick="javascript:sendQuestMember()">クエストメンバー送信</button>'));
        //メッセージ表示
        showMessage([phase,type,2]);
    }
}

//クエストメンバー送信
function sendQuestMember(){
    var members = [];
    $(':checked').each(function(no,elem){
        members.push($(elem).val());
    });

    var required = $('.right-top .memberNo')
    if(members.length == tableInfo.questmember[questNo - 1]){
        sendMessage(1,members);
        removeForm();
    }else{
        console.log('だめ');
    }
}

//クエストメンバー仮表示
function showMember(info){
	questMember = info;
	nameMessage = []

	for(var order in questMember){
		$('tr.' + questMember[order] + ' td.name').addClass('selected');
		nameMessage.push(memberInfo.players[questMember[order]] + 'さん');
	}
	var replace = nameMessage.join('、');
    showMessage([phase,type],{name:replace});
}

//承認フォーム表示
function approveMember(info){
    $('.left .radio').append($('<label>承認<input type="radio" name="approve" value="for"></lable>'));
    $('.left .radio').append($('<label>却下<input type="radio" name="approve" value="against"></lable>'));
    $('.left .button').append($('<button type="button" onclick="javascript:sendApprove()">投票</button>'));
    showMessage([phase,type]);
}

//承認送信
function sendApprove(){
    var info = $(':checked').val();
    if(typeof info == 'undefined'){
        console.log('だめ');
    }else{
        sendMessage(2, info);;
        removeForm();
    }
}

//承認結果表示
function showVoteResult(info){
	//TODO 票表示
	if(info[0]){
		var memberArray = [];
		for(var order in questMember){
			memberArray.push(memberInfo.players[questMember[order]]);
		}
		$('.right-top table tr.member td.' + questNo).append(memberArray.join('<br>'));
	}
	var resultList = info[1];
	for(var order in resultList){
		var result = resultList[order] == 'for' ? '承認' : '却下';
		showMessage([phase,type,2],{'name':memberInfo.players[order],'result':result})
	}
	var approved = info[0] ? '承認' : '却下';
	showMessage([phase,type,1],{'result':approved})


}

//クエストフォーム表示
function doQuest(info){
    var names = [];
    for(var order in questMember){
    	names.push(memberInfo.players[questMember[order]] + 'さん');
    }
    replace = names.join('、')
    showMessage([phase,type,1],{name:replace});
    if(info){
        $('.left .radio').append($('<label>成功<input type="radio" name="execute" value="success"></lable>'));
        if(memberInfo.side == "dark"){
            $('.left .radio').append($('<label>失敗<input type="radio" name="execute" value="failed"></lable>'));
        }
        $('.left .button').append($('<button type="button" onclick="javascript:sendResult()">実行</button>'));
        showMessage([phase,type,2])
    }
}

//クエストカード送信
function sendResult(){
    var info = $(':checked').val();
    if(typeof info == 'undefined'){
        console.log('だめ');
    }else{
        sendMessage(3, info);
        removeForm();
    }
}

//クエスト結果表示
function showQuestResult(info){
	if(info[0]){
		showMessage([phase,type,1]);
		result = '成功'
	}else{
		showMessage([phase,type,2],{number:info[1].failed});
		result = '失敗（' + info[1].failed + '枚）';
	}


	var resClass = info[0] ? 'success' : 'failed';
	$('.right-top table tr.result td.' + questNo).addClass(resClass).append(result);
}

//湖の乙女フォーム表示
function doLady(info){
	if(info.execute){
		for(var order in info.list){
			$('tr.' + info.list[order] + ' td.selectArea').append($('<input type="radio" name="lady" value="' + info.list[order] + '">'));
		}
		$('.left .button').append($('<button type="button" onclick="javascript:checkSide()">陣営を見る</button>'));
		$('.memberInfo tbody tr.' + info.list[order] + ' td.selectArea')
        $('.memberInfo :hidden').fadeIn(500);
		showMessage([phase,type,2]);
	}else{
		showMessage([phase,type,1],{
			'name':memberInfo.players[ladyDoer]
		});
	}
}

//湖の乙女を実行する
function checkSide(){
    var info = $(':checked').val();
    ladyTarget = info;
    if(typeof info == 'undefined'){
        console.log('だめ');
    }else{
        sendMessage(4, info);
        removeForm();
    }
}

//湖の乙女結果表示
function showLadyResult(info){
	$('.memberInfo tbody td.lady').each(function(no, elem){
		if($(elem).text() == '●'){
			$(elem).text('〇');
		}
	});
	$('tr.' + info + ' td.lady').text('●');
	showMessage([phase,type],{
		'name1':memberInfo.players[ladyDoer],
		'name2':memberInfo.players[info]
	});
	ladyDoer = info;
}

//湖の乙女陣営表示
function showLadySide(info){
	var oldSide = $('.memberInfo tbody tr.' + ladyTarget + ' td.side').text()
	var sideName = getSideName(info);
	if(oldSide = getRoleName('Merlin')){
		sideName = ' ' + sideName;
	}else if(oldSide = getSideName('dark')){
		sideName = '';
	}
	$('tr.' + ladyTarget + ' td.side').addClass(info).append(sideName);
	setSideClass(ladyTarget, info);
	showMessage([phase,type],{
		'name':memberInfo.players[ladyTarget],
		'side':getSideName(info)
	});
}

//マーリン指名フォーム表示
function selectMerlin(info){
	showMessage([6,'judge',1],{'side':'<span class="sucess">正義</span>'});
	showMessage([phase,type,1],{'name':memberInfo.players[info.murder]});
	murder = info.murder;
	if(info.isMurder){
		for(var order in info.justice){
			$('tr.' + info.justice[order] + ' td.selectArea').append($('<input type="radio" name="lady" value="' + info.justice[order] + '">'));
		}
		$('.left .button').append($('<button type="button" onclick="javascript:sendMeriln()">暗殺</button>'));
        $('.memberInfo :hidden').fadeIn(500);
        showMessage([phase,type,2]);
	}
}

//マーリン送信
function sendMeriln(){
    var info = $(':checked').val();
    if(typeof info == 'undefined'){
        console.log('だめ');
    }else{
        sendMessage(5, info);
        removeForm();
    }
}

//マーリン指名された人表示
function showMerlinResult(info){
	showMessage([phase,type],{
		'name1':memberInfo.players[murder],
		'name2':memberInfo.players[info]
	});
}

//マーリン結果表示
function showMurderResult(info){
	var no = info.isMerlin ? 1 : 2;
	showMessage([phase,type,no],{'name':memberInfo.players[info.murdered]});
}

//最終結果表示
function showJudge(info){
	var className = info.winner == 'justice' ? 'success' : 'failed';
	showMessage([phase,type,1],{'side':getSideName(info.winner),'class':className});
	var result = info.table
	for(var member in result){
		className = result[member].side == 'justice' ? 'success' : 'failed';
		showMessage([phase,type,2],{
			'name':memberInfo.players[member],
			'side':getSideName(result[member].side),
			'role':getRoleName(result[member].role),
			'class':className,
		})
	}
}

//メッセージ送信
function sendMessage(phaseNo, info){
    var message = {
        'phase':phaseNo,
        'info':info
    }
    conn.send(JSON.stringify(message));

}

//フォーム削除
function removeForm(){
    $('input').remove();
    $('button').remove();
    $('label').remove();
}

//陣営クラス設定
function setSideClass(order, side){
	$('.memberInfo tbody tr.' + order + ' td.side').addClass(side);
}

//メッセージ表示
function showMessage(keys, replace){
	message = messagebag.getMessage(keys, replace);
	formerMessage = messagebag.getFormerMessage();
	$('div.right-bottom div.message-area').append('<p><span class="former">' + formerMessage + '-></span>' + '<span class="main">' + message + '</span></p>');
	$('div.right-bottom div.message-area').append('<hr>');
	$('div.message-area').scrollTop($('div.message-area')[0].scrollHeight);
}

//ゲーム進行ボタン表示
function showDoAction(){
    $('.left .button').append($('<button type="button" onclick="javascript:doAction()">ゲーム進行</button>'));
}

//ゲーム進行
function doAction(){
	conn.send(JSON.stringify({phase:99}));
    $('button').remove();
}

//即座に進行するか？
function doImmediate(e){
	if(JSON.parse(e.data).type == 'doAction'){
		return true;
	}else if($.inArray(type,['prompt','ladyResult','merlinResult','judge']) != -1){
		return true;
	}else if(phase == 6){
		return true;
	}else{
		return false;
	}
}

//陣営名取得
function getSideName(side){
	return side == 'justice' ? '正義' : '邪悪';
}

//役職名取得
function getRoleName(role){
	var roles = {
		Merlin:'マーリン',
		Perceval:'パーシバル',
		Mordred:'モードレッド',
		Morgan:'モルガナ',
		Oberon:'オベロン',
		Murder:'暗殺者',
	}
	var result = typeof roles[role] == 'undefined' ? 'なし' : roles[role];

	return result;
}