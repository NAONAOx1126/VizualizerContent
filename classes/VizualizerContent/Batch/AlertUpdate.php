<?php

/**
 * Copyright (C) 2012 Vizualizer All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Naohisa Minagawa <info@vizualizer.jp>
 * @copyright Copyright (c) 2010, Vizualizer
 * @license http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 * @since PHP 5.3
 * @version   1.0.0
 */

/**
 * ページの更新チェックを行うバッチです。
 *
 * @package VizualizerContent
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class VizualizerContent_Batch_AlertUpdate extends Vizualizer_Plugin_Batch
{
    public function getName(){
        return "Alert Page Update";
    }

    public function getFlows(){
        return array("getUpdates", "sendUpdates");
    }

    /**
     * アップデート情報を取得するメソッドです。
     * @param $params バッチ自体のパラメータ
     * @param $data バッチで引き回すデータ
     * @return バッチで引き回すデータ
     */
    protected function getUpdates($params, $data){
        $loader = new Vizualizer_Plugin("Content");
        $model = $loader->loadModel("PageItem");
        $data["created"] = $model->findAllBy(array("created" => "1"));
        $data["updated"] = $model->findAllBy(array("ne:created" => "1", "updated" => "1"));
        $data["deleted"] = $model->findAllBy(array("gt:deleted" => "5"));
        return $data;
    }

    /**
     * 更新情報を送信するメソッド
     * @param $params バッチ自体のパラメータ
     * @param $data バッチで引き回すデータ
     * @return バッチで引き回すデータ
     */
    protected function sendUpdates($params, $data){
        $sendTo = Vizualizer_Configure::get("sendUpdates");

        // トランザクションの開始
        $connection = Vizualizer_Database_Factory::begin("content");

        try {
            $body = "";
            foreach($sendTo["target"] as $target => $title){
                $body .= "■".$title."\r\n";
                $count = 0;
                foreach($data[$target] as $item){
                    $count ++;
                    $body .= "\t".$item->item_name."（".$item->item_title."）\r\n";
                    if($item->deleted > 5){
                        $item->delete();
                    }else{
                        $item->created = 0;
                        $item->updated = 0;
                        $item->save();
                    }
                }
                if($count == 0){
                    $body .= "\t対象はありません\r\n";
                }
                $body .= "\r\n";
            }

            foreach($sendTo["email"] as $email){
                $mail = new Vizualizer_Sendmail();
                $mail->setFrom(Vizualizer_Configure::get("site_email"), Vizualizer_Configure::get("site_name"));
                $mail->setTo($email);
                $mail->setSubject(Vizualizer_Configure::get("site_name")."通知");
                $mail->addBody($body);
                $mail->send();
            }

            // エラーが無かった場合、処理をコミットする。
            Vizualizer_Database_Factory::commit($connection);
        } catch (Exception $e) {
            Vizualizer_Database_Factory::rollback($connection);
            throw new Vizualizer_Exception_Database($e);
        }
        return $data;
    }
}
