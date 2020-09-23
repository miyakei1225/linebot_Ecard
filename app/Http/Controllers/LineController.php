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
		$lineAccessToken = env("LINE_ACCESS_TOKEN", "");
		$lineChannelSecret = env("LINE_CHANNEL_SECRET", "");

		// 署名のチェック
		$signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
		if (!SignatureValidator::validateSignature($request->getContent(), $lineChannelSecret, $signature)) {
			Log::debug("不正なアクセスがありました");
			return;
		}

		$httpClient = new CurlHTTPClient($lineAccessToken);
		$lineBot = new LINEBot($httpClient, ["channelSecret" => $lineChannelSecret]);

		try {
			// イベント取得
			$events = $lineBot->parseEventRequest($request->getContent(), $signature);

			foreach ($events as $event) {
				$message = $event->getText();
				$replyToken = $event->getReplyToken();

				if ($message == "@開始") {
					// ゲーム開始
					// Game::start(); 的な?
					// 別ファイルに処理を書く

					$lineBot->replyText($replyToken, "ゲームを開始します");
				} else if ($message == "@終了") {
					// ゲーム終了
					// Game::end(); 的な?
					// 別ファイルに処理を書く

					$lineBot->replyText($replyToken, "お疲れ様でした！\nゲームを終了します");
				} else {
					// とりあえずおうむ返し
					$lineBot->replyText($replyToken, $message);
				}
			}

		} catch (\Exception $e) {
			Log::debug($e->getMessage());
			return;
		}
	}

}
