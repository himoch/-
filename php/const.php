<?php
return
[
    'table' =>
    [
      '5' =>   ['darksides' => 2, 'questmember' => [2, 3, 2, 3, 3], 'twoFails'=> false],
      '6' =>   ['darksides' => 2, 'questmember' => [2, 3, 4, 3, 4], 'twoFails'=> false],
      '7' =>   ['darksides' => 3, 'questmember' => [2, 3, 3, 4, 4], 'twoFails'=> true],
      '8' =>   ['darksides' => 3, 'questmember' => [3, 4, 4, 5, 5], 'twoFails'=> true],
      '9' =>   ['darksides' => 3, 'questmember' => [3, 4, 4, 5, 5], 'twoFails'=> true],
      '10' =>  ['darksides' => 4, 'questmember' => [3, 4, 4, 5, 5], 'twoFails'=> true]
    ],
    'side' =>
    [
        'justice' => 0,
        'dark'    => 1
    ],
    'role' =>
    [
        'Merlin'   => '0',
        'Perceval' => '1',
        'Mordred'  => '2',
        'Morgan'   => '3',
        'Oberon'   => '4',
        'murder'   => '5',
        'justice'  => '',
        'dark'     => '',
    ],
    'justiceRoles' => ['Merlin','Perceval'],
    'message' =>
    [
        'deal' =>
            [
                'side' => 'あなたの陣営は:sideです。',
                'role' => 'あなたの役職は:roleです。',
                'noRrole' => 'あなたは役職がありません。',
                'Merlin' => "邪悪陣営のプレイヤーを知ることができます。邪悪陣営のプレイヤーは:namesです。",
                'Perceval' => 'マーリンが誰なのか知ることができます。ただし邪悪陣営にモルガナがいる場合、その人もマーリンとして見えてしまいます。マーリンのプレイヤーは:name[か:nameのどちらか]です。',
                'Mordred' => 'マーリンから自分の正体を隠すことができます。',
                'Morgan' => 'パーシバルのプレイヤーから見て、あなたもマーリンであるかのように見えます。',
                'Oberon' => '邪悪陣営から見て、あなたが邪悪陣営のプレイヤーだとわかりません。あなたも、他の邪悪陣営のプレイヤーを見ることができません。',
                'murder' => '最後のマーリン指名フェイズの際に、決定権を持っています。',
                'dark' => '他の邪悪陣営のプレイヤーは:namesです。',
                'justice' => '邪悪陣営のプレイヤーがだれか推理しましょう！',
            ],
        'selectMember' =>
            [
                'leader' => 'リーダーは:nameさんです。クエストに参加するメンバーを選んでください。',
                'other'  => 'リーダーは:nameさんです。',
            ],
        'vote' => 'クエストメンバーに:nameさんが指名されました。承認するか却下するか選んでください。',
        'voteResult' => 'メンバーは:resultされました。',
        'execute' =>
            [
                'justice' => 'クエストを実行してください。',
                'dark'    => 'クエストを成功させるか失敗させるか選んでください。',
                'other'   => ':namesさんがクエストを実行しています。',
            ],
        'questResult' => 'クエストは:resultしました。',
        'lady' =>
            [
                'lady' => ':nameさんは、陣営を知りたいメンバーを選択してください。',
                'other' => ':nameさんが、湖の乙女の能力を実行しています。',
            ],
        'ladyResult' => ':nameさんの陣営は;sideです。',
        'judge' => ':side陣営の勝利です！',
        'answer' => ':name　陣営：:side　役職：:role',
        'selectMerlin' => '邪悪陣営に逆転のチャンス！邪悪陣営の皆さんはマーリンがだれか相談してください。暗殺者の:nameさんは、決定したらマーリンを指名してください。',
    ],
    'query' =>
    [
        'makeTable' =>0,
        'addPlayer' =>1,
        'decideMember' =>2,
        'vote' =>3,
        'action' =>4,
        'lady' =>5,
        'murder' =>6
    ],
    'phase' =>
    [
        '参加受付' => 0,
        'メンバー選択' => 1,
        '承認' => 2,
        'クエスト実行' => 3,
        '湖の乙女' => 4,
        '暗殺' => 5,

    ]

]
;