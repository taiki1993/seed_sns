<?php 
//セッションの初期化
//セッションを使うページでは全てにこの記述をいれる
session_start();

require('../dbconnect.php');

$error = array();


if(isset($_POST) && !empty($_POST)){
  //エラー項目の確認
  if($_POST['nick_name'] == '') {
    $error['nick_name'] = 'blank';
  }
  if($_POST['email'] == ''){
    $error['email'] = 'blank';
  }
  if($_POST['password'] == ''){
    $error['password'] = 'blank';
  }else if(strlen($_POST['password'])<4){
    $error['password'] = 'length';
  }
  
  $fileName = $_FILES['picture_path']['name'];
  if (!empty($fileName)) {
    $ext = substr($fileName, -3);
    if($ext !='jpg' && $ext != 'gif'){
      $error['picture_path'] = 'type';
    }
  }



  //重複チェック
  if (empty($error)) {
    $sql = sprintf('SELECT COUNT(*) AS cnt FROM `members` WHERE `email` = "%s"',mysqli_real_escape_string($db, $_POST['email']));
    $record = mysqli_query($db, $sql) or die(mysqli_error());
    //連想配列としてSQL実行結果を受け取る
    $table = mysqli_fetch_assoc($record);
    if ($table['cnt']>0) {
      $error['email'] = 'duplicate';
      //duplicateは重複
    }

  }

  if(empty($error)){
    //エラーが無かったら処理する
    $picture_path = date('YmdHis') . $fileName;
    move_uploaded_file($_FILES['picture_path']['tmp_name'], '../member_picture/'. $picture_path);

    $_SESSION['join'] = $_POST;
    $_SESSION['join']['picture_path'] = $picture_path;

    //check.phpへ遷移
    header('Location: check.php');
    //これより以下のコードを処理しないようにexit()で抜ける
    exit();
  }

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'rewrite') {
  $_POST = $_SESSION['join'];
  
}

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
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->


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
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>会員登録</legend>
        <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
          <!-- ニックネーム -->
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
              <?php if (isset($_POST['nick_name'])) {
                echo sprintf('<input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun" value="%s">',htmlspecialchars($_POST['nick_name'], ENT_QUOTES, 'UTF-8'));
              }else{
                echo '<input type="text" name="nick_name" class="form-control" placeholder="例：Seedkun" value="">';
              } ?>
              <?php if(isset($error['nick_name']) && $error['nick_name'] == 'blank'): ?>
              <p class="error">* ニックネームを入力してくれ</p>
              <?php endif; ?>
            </div>
          </div>
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <?php if (isset($_POST['email'])) {
                echo sprintf('<input type="text" name="email" class="form-control" placeholder="例： Seed kun@nexseed" value="%s">',htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'));
              }else{
                echo '<input type="text" name="email" class="form-control" placeholder="例： Seedkun@nexseed" value="">';
              } ?>
              <?php if(isset($error['email'])&&$error['email'] == 'blank'): ?>
              <p class="error">* メールアドレスを入れてくれ。</p>
              <?php endif; ?>
              <?php if(isset($error['email'])&&$error['email'] == 'duplicate'): ?>
              <p class="error">* 登録されとるよ。</p>
              <?php endif; ?>
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <?php if (isset($_POST['password'])){
                echo sprintf('<input type="password" name="password" class="form-control" placeholder="" value="%s">',htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'));
              }else{
                echo '<input type="password" name="password" class="form-control" placeholder="" value="">';
              } ?>
              <?php if(isset($error['password'])&&$error['password'] == 'blank'): ?>
              <p class="error">* パスワードを入力しろ</p>
              <?php endif; ?>
              <?php if(isset($error['password'])&&$error['password'] == 'length'): ?>
              <p class="error">* パスワードを４文字以上でいれろ</p>
              <?php endif; ?>
            </div>
          </div>
          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
              <input type="file" name="picture_path" class="form-control">
              <?php if(isset($error['picture_path']) && $error['picture_path'] == 'type'): ?>
              <p class = "error">* 写真などはちゃんとしてくれ</p>
            <?php endif; ?>
            <?php if(!empty($error)): ?>
              <p class="error">* 恐れ入りますが画像をやりなおして</p>
            <?php endif; ?>
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
