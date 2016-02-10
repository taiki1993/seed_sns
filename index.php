<?php 

//ログインした状態だから
session_start();

require('dbconnect.php');


function makeLink($value){
  return mb_ereg_replace('(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)','<a href="\1\2" target="_blank">\1\2</a>',  $value);
}

//ログイン中の条件　
//セッションにmember_idが入っている
//最後のログイン時間が１時間以内
//3600は60*60の事
if (isset($_SESSION['member_id']) && $_SESSION['time'] + 3600 > time()) {
  //セッションの時間を更新
  $_SESSION['time'] = time();

  $sql = sprintf('SELECT * FROM `members` WHERE `member_id` =%d', mysqli_real_escape_string($db,$_SESSION['member_id']));

  $record = mysqli_query($db,$sql) or die (mysqli_error());

  $member = mysqli_fetch_assoc($record);


}else{
  header('Location: login.php');
  exit();
}

//つぶやくボタンをクリックしたとき
if (!empty($_POST)) {
  //下のところがtweetになっているから
  if($_POST['tweet'] !=''){
    $sql = sprintf('INSERT INTO `tweets`SET `tweet`="%s", `member_id`=%d, `reply_tweet_id` =%d, `created`=now()',
      mysqli_real_escape_string($db, $_POST['tweet']),
      mysqli_real_escape_string($db, $member['member_id']),
      mysqli_real_escape_string($db, $_POST['reply_tweet_id'])
      );


    mysqli_query($db, $sql) or die(mysqli_error());

    //リロードの重複登録を防ぐため
    header('Location: index.php');
    exit();

  }
}

    //ページング処理
    $page = '';
    if (isset($_REQUEST['page'])) {
        $page = $_REQUEST['page'];
    }
    //通常、index.phpが表示された時
    if($page == ''){
      $page = 1;
    }

    // max関数：()内に指定した複数のデータから、一番大きい値を返す
    //①表示する正しいページの数値（Min）を設定
    $page = max($page, 1);

    //②必要なページ数を計算する
    $sql = sprintf('SELECT COUNT(*) AS cnt FROM `tweets`');
    $recordSet = mysqli_query($db, $sql) or die(mysqli_error($db));
    $table = mysqli_fetch_Assoc($recordset);

    //ceil()関数：切り上げ
    $maxPage = ceil($table['cnt'] /5);

    //③表示する正しいページ数の数値（Max）を設定
    $page = min($page, $maxPage);

    //④ページに表示する件数だけ取得
    $start = ($page -1) * 5;
    $start = max(0, $start);





//投稿内容を取得する。
$sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM `tweets` t, `members` m WHERE t.member_id = m.member_id ORDER BY t.created DESC LIMIT %d, 5',
  $start);
$tweets = mysqli_query($db,$sql) or die(mysqli_error($db));

if (isset($_REQUEST['res'])) {
  //とってきたい内容は、ニックネームとつぶやき内容
  $sql = sprintf('SELECT m.nick_name, m.picture_path, t.* FROM `tweets` t, `members` m WHERE t.member_id = m.member_id AND t.tweet_id = %dSELECT m.nick_name, m.picture_path, t.* FROM `tweets` t, `members` m WHERE t.member_id = m.member_id ORDER BY t.created DESC ORDER BY t.created DESC',
    mysqli_real_escape_string($db, $_REQUEST['res'])
  );
  $record = mysqli_query($db, $sql); 
  $table = mysqli_fetch_assoc($record);
  $tweet = '>> @'.$table['nick_name'].' '.$table['tweet'];
}

 ?>


<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.html">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo h($member['nick_name']); ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
                <?php if(isset($tweet)): ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo htmlspecialchars($tweet, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <input type="hidden" name="reply_tweet_id" value="<?php echo h($_REQUEST['res']); ?>">
                <?php else: ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php endif; ?>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <form method="get" action="" class="form-horizontal" role="form">
        <?php if (isset($_GET['search_word']) && !empty($_GET['search_word'])): ?> 
        <input type="text" name="search_word" value="<?php echo h($_GET['search_word']); ?>">
      <?php else: ?>
        <input type="text" name="search_word" value="">
      <?php endif; ?>
        <input type="submit" class="btn btn-success btn-xs" value="検索">
        &nbsp;&nbsp;&nbsp;&nbsp;
        <?php if($page > 1): ?>
        <li><a href="index.php?page=<?php print($page-1); ?>" class="btn btn-default">前</a></li>
        <?php else: ?>
          <li>前</li>
        <?php endif; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <?php if($page < $maxPage): ?>
          <li><a href="index.php?page=<?php print($page+1); ?>" class="btn btn-default">次</a></li>
        <?php else: ?>
          <li>次</li>
        <?php endif; ?>
      </form>
 <!-- ここでつぶやいた内容を繰り返し表示する -->
        <?php while ($tweet = mysqli_fetch_assoc($tweets)): ?>
        <div class="msg">
        <form method="get" action="" class="form-horizontal" role="form">
          <img src="member_picture/<?php echo htmlspecialchars($tweet['nick_name'], ENT_QUOTES, 'UTF-8'); ?>" width="48" height="48">
          <p>
            <?php echo makeLink(h($tweet['tweet'])); ?>
            <span class="name"> (<?php echo h($tweet['nick_name']); ?>) </span>
            [<a href="index.php?res=<?php echo h($tweet['tweet_id']); ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              <?php echo htmlspecialchars($tweet['created'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <?php if($tweet['reply_tweet_id'] > 0): ?>
            <a href="view.php?tweet_id=<?php echo htmlspecialchars($tweet['reply_tweet_id'], ENT_QUOTES, 'UTF-8'); ?>"> | 返信元のつぶやき</a>
          <?php endif; ?>
            [<a href="#" style="color: #00994C;">編集</a>]
            <?php if($member['member_id'] == $tweet['member_id']): ?>
            [<a href="delete.php?tweet_id=<?php echo h($tweet['tweet_id']); ?>" style="color: #F33;">削除</a>]
          <?php endif; ?>
          </p>
        </form>
        </div>
      </div>
</div>
</div>

<!--     </div>
  </div>
 -->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
