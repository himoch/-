var messagebag = {
	messages:{
		0:{
			prompt:{
				1:':nameさんが円卓の騎士に参加しました。',
				2:'円卓へようこそ！'
			},
			result:{
				1:'あなたの陣営は<span class=":class">:side</span>です。',
				2:'あなたの役職は<span class=":class">:role</span>です。',
				3:'あなたの役職はありません。'
			},
		},
		1:{
			prompt:{
				1:'次のリーダーは:nameさんです。（投票トラック:no）',
				2:'クエストメンバーを選択してください。',
			},
			result:'クエストメンバーに:nameが選択されました。',
		},
		2:{
			prompt:'承認または却下を選択してください。',
			result:{
				1:'クエストメンバーは:resultされました。',
				2:':name：:result',
			},
		},
		3:{
			prompt:{
				1:':nameがクエストを実行します。',
				2:'成功か失敗を選択してください。',
			},
			result:{
				1:'クエストは<span class="success">成功</span>しました。',
				2:'クエストは<span class="failed">失敗</span>しました。（失敗：<b>:number</b>枚）',
			}
		},
		4:{
			prompt:{
				1:':nameさんが湖の乙女を実行します。',
				2:'陣営を見る人を選択してください。',
			},
			ladyResult:':nameさんの陣営は:sideです。',
			result:':name1さんが:name2さんの陣営を確認しました。'
		},
		5:{
			prompt:{
				1:'邪悪陣営に逆転のチャンスです！暗殺者:nameさんがマーリンを指名します。',
				2:'マーリンを指名してください。',
			},
			merlinResult:':name1さんが:name2さんを指名しました。',
			result:{
				1:':nameさんは<span class="failed">マーリンです。</span>',
				2:':nameさんは<span class="success">マーリンではありません。</span>',
			},
		},
		6:{
			judge:{
				1:'<span class=":class">:side</span>陣営の勝利です！',
				2:'<b>:name</b>：陣営：<span class=":class">:side</span>　役職：:role',
			}
		}
	},
	phaseName:{
		1:'メンバー選択フェイズ',
		2:'承認フェイズ',
		3:'クエスト実行フェイズ',
		4:'湖の乙女フェイズ',
		5:'マーリン指名フェイズ',
		6:'結果発表'
	},
	getMessage:function(keys,replace){
		var message = this.messages;
		for(var key in keys){
			message = message[keys[key]];
		}

		for(var key in replace){
			if(keys[0] == 6 || keys[0] == 0 && keys[1] == 'result'){
				//結果発表もしくは役職配布なら<b>をつけない
				message = message.replace((':' + key), replace[key]);
			}else{
				message = message.replace((':' + key), '<b>' + replace[key] + '</b>');
			}
		}
		return message;
	},
	getFormerMessage:function(){
		var message;
		if(phase == 0){
			if(type == 'prompt'){
				message = '入室メッセージ';
			}else{
				message = '役職配布';
			}
		}else{
			message = '第' + questNo + 'クエスト　' + this.phaseName[phase];
		}
		return message;
	}
}