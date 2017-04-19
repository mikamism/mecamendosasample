<?php
// ライブラリの読み込み
require_once __DIR__ . '/vendor/autoload.php';

// CurlHTTPClientのインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
// LINEBotのインスタンス化
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

// LINEからPOSTされたデータのパース
$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
  // POSTデータの格納し、リクエストがLINE Plagform以外の場合は空にする
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log("parseEventRequest failed. InvalidSignatureException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log("parseEventRequest failed. UnknownEventTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log("parseEventRequest failed. UnknownMessageTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log("parseEventRequest failed. InvalidEventRequestException => ".var_export($e, true));
}

// 格納したPOSTされた配列を一つずつ取り出す
foreach ($events as $event) {
  // 取り出したものにMessageEventがなかったらログを吐き処理をスキップ
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }
  // 取り出したものにTextMessageがなかったらログを吐き処理をスキップ
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
    error_log('Non text message has come');
    continue;
  }
  /*
  // ReplyTokenへ取得したテキストを返す
  //$bot->replyText($event->getReplyToken(), $event->getText());

  // プロフィール情報を取得
  $profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
  // プロフィールから表示名を取得しメッセージへセット
  $message = $profile["displayName"] . "さん、おはようございます！今日も頑張りましょう！";
  // 返答内容にテキストとスタンプをセット
  $bot->replyMessage($event->getReplyToken(),
    (new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder())
      ->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message))
      ->add(new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 114))
  );
  */
  replyTextMessage($bot, $event->getReplyToken(), "TextMessage");
}

function replyTextMessage($bot, $replyToken, $text) {
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

?>
