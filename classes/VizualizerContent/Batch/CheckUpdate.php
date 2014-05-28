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
class VizualizerContent_Batch_CheckUpdate extends Vizualizer_Plugin_Batch
{
    public function getName(){
        return "Check Page Update";
    }

    public function getFlows(){
        return array("checkPage", "checkPageItems");
    }

    /**
     * ページ自体のチェックを行うメソッド
     * @param $params バッチ自体のパラメータ
     * @param $data バッチで引き回すデータ
     * @return バッチで引き回すデータ
     */
    protected function checkPage($params, $data){
        // パラメータからURLを取得
        if(count($params) >= 4){
            $url = $params[3];
            $content = file_get_contents($url);
            // URLに該当するコンテンツがある場合には、URL情報を登録
            if(!empty($content)){
                // コンテンツをphpQueryに読み込み
                $html = phpQuery::newDocument($content);

                // トランザクションの開始
                $connection = Vizualizer_Database_Factory::begin("content");

                try {
                    $loader = new Vizualizer_Plugin("Content");
                    $model = $loader->loadModel("Page");
                    $model->findByPageUrl($url);

                    $model->page_url = $url;
                    $model->page_title = trim($html['title']->text());
                    $model->save();
                    $data["page"] = $model;
                    $data["content"] = $html;

                    // エラーが無かった場合、処理をコミットする。
                    Vizualizer_Database_Factory::commit($connection);
                } catch (Exception $e) {
                    Vizualizer_Database_Factory::rollback($connection);
                    throw new Vizualizer_Exception_Database($e);
                }
            }
        }
        return $data;
    }

    /**
     * ページの内部要素のチェックを行うメソッド
     * @param $params バッチ自体のパラメータ
     * @param $data バッチで引き回すデータ
     * @return バッチで引き回すデータ
     */
    protected function checkPageItems($params, $data){
        $page = $data["page"];
        $selectors = Vizualizer_Configure::get("checkSelectors");

        foreach($selectors as $selector => $configs){
            if($page->page_id > 0){
                // トランザクションの開始
                $connection = Vizualizer_Database_Factory::begin("content");

                // ページ項目モデルを生成
                $loader = new Vizualizer_Plugin("Content");
                $model = $loader->loadModel("PageItem");

                // ページ内の項目全体に削除フラグを立てる
                $items = $model->findAllByPageId($page->page_id);
                foreach($items as $item){
                    $item->deleted ++;
                    $item->save();
                }

                try {
                    $html = $data["content"];
                    $items = $html[$selector];
                    foreach($items as $item){
                        $values = array();
                        foreach($configs as $key => $config){
                            switch($config["type"]){
                                case "html":
                                    $values[$key] = pq($item)->find($config["selector"])->html();
                                    break;
                                case "attr":
                                    $values[$key] = pq($item)->find($config["selector"])->attr($config["attr"]);
                                    if($config["attr"] == "href" || $config["attr"] == "src"){
                                        $info = parse_url($values[$key]);
                                        if(!array_key_exists("scheme", $info)){
                                            // URLで無い場合はURL化する。
                                            $info2 = parse_url($page->page_url);
                                            $info2["path"] = pathinfo($info2["path"]);
                                            if(substr($values[$key], 0, 1) == "/"){
                                                $values[$key] = $info2["scheme"]."://".$info2["host"].$values[$key];
                                            }elseif(array_key_exists("extension", $info2["path"])){
                                                $values[$key] = $info2["scheme"]."://".$info2["host"].(!empty($info2["path"]["dirname"])?$info2["path"]["dirname"]:"")."/".$values[$key];
                                            }else{
                                                $values[$key] = $info2["scheme"]."://".$info2["host"].(!empty($info2["path"]["dirname"])?$info2["path"]["dirname"]:"").(!empty($info2["path"]["basename"])?"/".$info2["path"]["basename"]:"")."/".$values[$key];
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    $values[$key] = pq($item)->find($config["selector"])->text();
                                    break;
                            }
                        }
                        if (!empty($values["title"]) && !empty($values["name"])) {
                            $model = $loader->loadModel("PageItem");
                            $model->findByItemTitle($page->page_id, $values["title"]);
                            if (!($model->page_item_id > 0)) {
                                $model->page_id = $page->page_id;
                                $model->item_selector = $selector;
                                $model->item_title = $values["title"];
                                $model->item_name = $values["name"];
                                $model->created = 1;
                            }
                            if ($model->item_value != $values["value"]) {
                                $model->item_value = $values["value"];
                                $model->updated = 1;
                            }
                            $model->deleted = 0;
                            $model->save();
                        }
                    }

                    // エラーが無かった場合、処理をコミットする。
                    Vizualizer_Database_Factory::commit($connection);
                } catch (Exception $e) {
                    Vizualizer_Database_Factory::rollback($connection);
                    throw new Vizualizer_Exception_Database($e);
                }
            }
        }
        return $data;
    }
}
