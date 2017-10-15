<?php
/**
 * Created by PhpStorm.
 * User: wamil
 * Date: 15-Oct-17
 * Time: 00:37
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class Note extends Model
{
    protected $fillable = [
        'user_id', 'unique_id', 'is_private', 'image_url', 'content', 'title'
    ];

    public function User()
    {
        return $this->belongsTo('App/User');
    }

    public function recognize2()
    {
        $url = "https://westcentralus.api.cognitive.microsoft.com/vision/v1.0/recognizeText";
        $data = array(
            'url' => "http://www.hnotes.org/api/uploads/images/$this->image_url",

            'handwriting' => 'true'
        );

        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
                'Ocp-Apim-Subscription-Key: 405521583a1143f2a2d4dd1014a22a26')
        );

//        $result = curl_exec($ch);

        $output = curl_exec($ch);

// close curl resource to free up system resources
//        curl_close($ch);

        var_dump($output);
    }

    public function recognize()
    {
        $url = "https://westcentralus.api.cognitive.microsoft.com/vision/v1.0/recognizeText";

        $client = new Client();
        $response = $client->request('POST', $url,
            [
                'json' => ['url' => "http://www.hnotes.org/api/uploads/images/$this->image_url"],
                'query' => ['handwriting' => 'true'],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Ocp-Apim-Subscription-Key' => '405521583a1143f2a2d4dd1014a22a26'
                ]
            ]);
        return $response->getHeaderLine('operation-location');
    }

    public function buildText()
    {
//        $json = $this->recognize();
//        $resultString = "";
//        foreach ($json->regions as $region) {
//            foreach ($region->lines as $line) {
//                foreach ($line->words as $word) {
//                    list($left, $top, $width, $height) = explode(',', $word->boundingBox);
//                    $resultString .= "<span style='position: absolute; top:$top; left: $left; font-size:" . $height . "px'>$word->text</span>";
//                }
//            }
//        }
//        return $resultString;

        $url = $this->recognize();
        $client = new Client();

        while (1)
        {
            $response = $client->request('GET', $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Ocp-Apim-Subscription-Key' => '405521583a1143f2a2d4dd1014a22a26'
                    ]
                ]);


            if ($response->getHeaderLine('content-length') > 20)
            {
                $json = $response->getBody();
                break;
            }
            else
            {
                sleep(10);
            }
        }

        $json = json_decode($json);

//        $resultString = "";
//        foreach ($json->regions as $region) {
//            foreach ($region->lines as $line) {
//                list($lineLeft, $lineTop, $lineWidth, $lineHeight) = explode(',', $line->boundingBox);
//                $resultString .= "<div style='position: absolute; top:$lineTop; left: $lineLeft; height:" . $lineHeight . "px; width:" . $lineWidth . "px; display: flex; flex-direction: row; flex-wrap: nowrap; align-items: flex-end;'>";
//                foreach ($line->words as $word) {
//                    list($left, $top, $width, $height) = explode(',', $word->boundingBox);
//
//                    $resultLeftMargin = $left - $lineLeft;
//                    $lineLeft += ($resultLeftMargin + $width);
//                    $resultString .= "<span style='margin-left:" . $resultLeftMargin . "px; font-size:" . $lineHeight . "px'>$word->text</span>";
//                }
//                $resultString .= "</div>";
//            }
//        }

        $resultString = "";
        $recognitionResult = $json->recognitionResult;

            foreach ($recognitionResult->lines as $line) {
                list($lineLeftTopX, $lineLeftTopY, $lineRightTopX, $lineRightTopY, $lineRightBottomX, $lineRightBottomY, $lineLeftBottomX, $lineLeftBottomY) = $line->boundingBox;
                $resultString .= "<div style='position: absolute; top:".$lineLeftTopY."px; left: ".$lineLeftTopX."px; height:" . ($lineLeftBottomY - $lineLeftTopY) . "px; width:" . ($lineLeftTopX - $lineRightTopX) . "px; display: flex; flex-direction: row; flex-wrap: nowrap; align-items: flex-end;'>";
                foreach ($line->words as $word) {
                    list($wordLeftTopX, $wordLeftTopY, $wordRightTopX, $wordRightTopY, $wordRightBottomX, $wordRightBottomY, $wordLeftBottomX, $wordLeftBottomY) =  $word->boundingBox;

                    $resultLeftMargin = $wordLeftTopX - $lineLeftTopX;
                    $lineLeftTopX += ($resultLeftMargin + ($wordLeftTopX - $wordRightTopX));
                    $resultString .= "<span style='margin-left:" . $resultLeftMargin . "px; font-size:" . (($wordLeftBottomY - $wordLeftTopY) + ($wordRightBottomY - $wordRightTopY))/2 . "px'>$word->text</span>";
                }
                $resultString .= "</div>";
            }

        return $resultString;
    }
}