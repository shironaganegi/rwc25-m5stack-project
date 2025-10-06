<?php
/**
 * index.php
 * アプリケーションのルートファイル。
 * ユーザーをホーム画面のディレクトリにリダイレクトする役割を持つ。
 */

// 訪問者を 'home/index.php' にリダイレクトする
// header() 関数は、ブラウザに HTTPヘッダー情報を送信する
header("Location: home/index.php");

// リダイレクトが実行された後、スクリプトの実行を停止する
exit;
?>