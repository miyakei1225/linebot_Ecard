<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

use Illuminate\Support\Facades\Log;

class LineController extends Controller
{
	public function webhook (Request $request) {
		$lineAccessToken = env('LINE_ACCESS_TOKEN', "");
		$lineChannelSecret = env('LINE_CHANNEL_SECRET', "");

		// 署名のチェック
		$signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
		if (!SignatureValidator::validateSignature($request->getContent(), $lineChannelSecret, $signature)) {
			// TODO: 不正アクセス
			return;
		}

		$httpClient = new CurlHTTPClient($lineAccessToken);
		$lineBot = new LINEBot($httpClient, ['channelSecret' => $lineChannelSecret]);

		try {
			// イベント取得
			$events = $lineBot->parseEventRequest($request->getContent(), $signature);
			foreach ($events as $event) {
				$message = $event->getText();
				$replyToken = $event->getReplyToken();
				$textMessage = new TextMessageBuilder($message);
				$lineBot->replyMessage($replyToken, $textMessage);
			}
		} catch (\Exception $e) {
			// TODO: 例外
			return;
		}
	}
}
