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
  // テキストを返す
  //replyTextMessage($bot, $event->getReplyToken(), "TextMessage");
  // 画像を返す
  //replyImageMessage($bot, $event->getReplyToken(), "https://" . $_SERVER["HTTP_HOST"] . "/imgs/original.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/preview.jpg");
  // 位置情報を返す
  //replyLocationMessage($bot, $event->getReplyToken(), "エイベックス", "東京都港区六本木1-6-1 泉ガーデンタワー38F", 35.66460959999999, 139.73950260000004);
  // スタンプを返す(https://devdocs.line.me/files/sticker_list.pdf)
  //replyStickerMessage($bot, $event->getReplyToken(), 1, 1);
  // 複数のメッセージを返す
  replyMultiMessage($bot, $event->getReplyToken(),
    new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("TextMessage"),
    new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder("https://" . $_SERVER["HTTP_HOST"] . "/imgs/original.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/preview.jpg"),
    new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder("エイベックス", "東京都港区六本木1-6-1 泉ガーデンタワー38F", 35.66460959999999, 139.73950260000004),
    new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(109, 1)
  );

}

// テキストの送信を行う
function replyTextMessage($bot, $replyToken, $text) {
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// 画像の送信を行う
function replyImageMessage($bot, $replyToken, $originalImageUrl, $previewImageUrl) {
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($originalImageUrl, $previewImageUrl));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// 位置情報の送信を行う
function replyLocationMessage($bot, $replyToken, $title, $address, $lat, $lon) {
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder($title, $address, $lat, $lon));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

// スタンプを送信する
function replyStickerMessage($bot, $replyToken, $packageId, $stickerId) {
  $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder($packageId, $stickerId));
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

//
function replyMultiMessage($bot, $replyToken, ...$msgs) {
  $builder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
  foreach($msgs as $value) {
    $builder->add($value);
  }
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

?>
