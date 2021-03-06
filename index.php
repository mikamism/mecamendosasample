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
  // PostbackEventの取得
  if ($event instanceof \LINE\LINEBot\Event\PostbackEvent) {
    replyTextMessage($bot, $event->getReplyToken(), "Postback受信「" . $event->getPostbackData() . "」");
    continue;
  }

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
  /*
  // 複数のメッセージを返す
  replyMultiMessage($bot, $event->getReplyToken(),
    new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("TextMessage"),
    new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder("https://" . $_SERVER["HTTP_HOST"] . "/imgs/original.jpg", "https://" . $_SERVER["HTTP_HOST"] . "/imgs/preview.jpg"),
    new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder("エイベックス", "東京都港区六本木1-6-1 泉ガーデンタワー38F", 35.66460959999999, 139.73950260000004),
    new \LINE\LINEBot\MessageBuilder\StickerMessageBuilder(1, 1)
  );
  */

  /*
  // Buttonsのテンプレートメッセージを返す
  replyButtonsTemplate($bot,
    $event->getReplyToken(),
    "お天気お知らせ - 今日は天気予報は晴れです",
    "https://" . $_SERVER["HTTP_HOST"] . "/imgs/template.jpg", // 画像のサイズは1:1.51
    "お天気お知らせ",
    "今日は天気予報は晴れです",
    new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder ( // ユーザに発現させる（アクションあり）
      "明日の天気", "tomorrow"),
    new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder (  // 文字列を送信するが表示させない
      "週末の天気", "weekend"),
    new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ( // URLを開かせる
      "Webで見る", "https://www.google.co.jp/#q=%E9%80%B1%E6%9C%AB%E3%81%AE%E5%A4%A9%E6%B0%97")
  );
*/

$columnArray = array();
  for($i = 0; $i < 5; $i++) {
    $actionArray = array();
    array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
      "ボタン" . $i . "-" . 1, "c-" . $i . "-" . 1));
    array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
      "ボタン" . $i . "-" . 2, "c-" . $i . "-" . 2));
    array_push($actionArray, new LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder (
      "ボタン" . $i . "-" . 3, "c-" . $i . "-" . 3));
    $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder (
      ($i + 1) . "日後の天気",
      "晴れ",
      "https://" . $_SERVER["HTTP_HOST"] .  "/imgs/template.jpg",
      $actionArray
    );
    array_push($columnArray, $column);
  }
  replyCarouselTemplate($bot, $event->getReplyToken(),"今後の天気予報", $columnArray);

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

//　複数のメッセージを送信する
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

// Buttionsテンプレートを送信する
function replyButtonsTemplate($bot, $replyToken, $alternativeText, $imageUrl, $title, $text, ...$actions) {
  $actionArray = array();
  foreach($actions as $value) {
    array_push($actionArray, $value);
  }
  $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
    $alternativeText,
    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder ($title, $text, $imageUrl, $actionArray)
  );
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

function replyConfirmTemplate($bot, $replyToken, $alternativeText, $text, ...$actions) {
  $actionArray = array();
  foreach($actions as $value) {
    array_push($actionArray, $value);
  }
  $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
    $alternativeText,
    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder ($text, $actionArray)
  );
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

function replyCarouselTemplate($bot, $replyToken, $alternativeText, $columnArray) {
  $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder(
  $alternativeText,
  new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder (
   $columnArray)
  );
  $response = $bot->replyMessage($replyToken, $builder);
  if (!$response->isSucceeded()) {
    error_log('Failed!'. $response->getHTTPStatus . ' ' . $response->getRawBody());
  }
}

?>
