<!doctype html>
<html>
    <head>
    <title>門</title>
    </head>
    <body>
        <a href="maketable.php">円卓を作成する</a><br>
        <?php require_once 'php\helper.php'; ?>
        円卓に参加する
            <form id="join" action="roundtable.php" method="post">
                <input type="hidden" name="messagetype" value="1">
                <label>名前　</label><input type="text" name="name"></label>
                <input type="hidden" name="tableID" value="">
            </form>
        <table>
            <?php foreach(helper\getTableList() as $row):?>
                <tr id="<?= $row->id ?>">
                    <td><?= $row->name ?></td>
                    <td>
                        <button type="button" onclick="javascript:send(<?= $row->id ?>);">参加する</button>
                    </td>
                </tr>
            <?php endforeach;?>
        </table>
    </body>
    <script type="text/javascript" src="jQuery-3.3.1.min.js"></script>
    <script type="text/javascript">
        function send(tableID){
            if($("#join [name='name']").val().trim() == ''){
                alert('名前を入力してください');
                return;
            }
            $("#join [name='tableID']").val(tableID);
            $("#join").submit();
        }

    </script>

</html>