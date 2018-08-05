<!doctype html>
<html>
    <head>
    <title>円卓作成</title>
    </head>
    <body>
        <?php require_once 'php\helper.php'; ?>
        <form method="post" action="roundTable.php">
            <input type="hidden" name="messagetype" value="makeTable">
            <input type="hidden" name="tableID" value="<?= helper\generateTableID() ?>">
            <table>
                <tr class="table-name">
                    <td>円卓名</td>
                    <td>
                        <input type="text" name="tableName">
                    </td>
                </tr>
                <tr class="member">
                    <td>人数</td>
                    <td>
                        <label><input type="radio" name="member" value="5" checked>５人</label>
                        <label><input type="radio" name="member" value="6">６人</label>
                        <label><input type="radio" name="member" value="7">７人</label>
                        <label><input type="radio" name="member" value="8">８人</label>
                        <label><input type="radio" name="member" value="9">９人</label>
                        <label><input type="radio" name="member" value="10">１０人</label>
                    </td>
                </tr>
                <tr class="role">
                    <td>役職</td>
                    <td>
                        <label><input type="checkbox" name="role[]" value="Merlin">マーリン</label>
                        <label><input type="checkbox" name="role[]" value="Perceval">パーシバル</label>
                        <label><input type="checkbox" name="role[]" value="Mordred">モードレッド</label>
                        <label><input type="checkbox" name="role[]" value="Morgan">モルガナ</label>
                        <label><input type="checkbox" name="role[]" value="Oberon">オベロン</label>
                    </td>
                </tr>
                <tr class="order">
                    <td>クエスト順</td>
                    <td>
                        <label><input type="radio" name="order" value="0" checked>順番通り</label>
                        <label><input type="radio" name="order" value="1">自由選択</label>
                    </td>
                </tr>
                <tr class="lady">
                    <td>湖の乙女</td>
                    <td>
                        <label><input type="checkbox" name="lady" value="on" disabled>有効（７人以上）</label>
                    </td>
                </tr>
                <tr class="name">
                    <td>あなたの名前</td>
                    <td>
                        <input type="text" name="name">
                    </td>
                </tr>
            </table>
            <button type="submit">作成</button>
        </form>
    </body>
    <script src="jquery-3.3.1.min.js"></script>
    <script type="text/javascript">

    $("tr.role input[value='Merlin']").click(function(){
        if(this.checked == false){
            $("tr.role input:not([value='Merlin'])").prop('checked',false);
        }
    });

    $("tr.role input[value='Perceval']").click(function(){
        if(this.checked == false){
            $("tr.role input[value='Morgan']").prop('checked',false);
        }
    });

    $("tr.role input[value='Morgan']").click(function(){
        if(this.checked == true){
            $("tr.role input[value='Perceval']").prop('checked',true);
        }
    });

    $("tr.role input:not([value='Merlin'])").click(function(){
        if(this.checked == true){
            $("tr.role input[value='Merlin']").prop('checked',true);
        }
    });

    $('tr.member input').click(function(){
        if(this.value < 7){
            $('tr.lady input').attr('disabled','disabled').prop('checked',false);
        }else{
        	$('tr.lady input').removeAttr('disabled');
        }
    });
    </script>
</html>