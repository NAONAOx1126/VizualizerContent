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
        // パラメータから日付を取得
        if(count($params) >= 4){
            $url = $params[3];
            $content = file_get_contents($url);
            // URLに該当するコンテンツがある場合には、URL情報を登録
            if(!empty($this->content)){
                // コンテンツをphpQueryに読み込み
                $html = phpQuery::newDocument($content);

                // トランザクションの開始
                $connection = Vizualizer_Database_Factory::begin("content");

                try {
                    $loader = new Vizualizer_Plugin("Content");
                    $model = $loader->loadModel("Page");
                    $model->findByUrl($this->url);

                    $model->url = $this->url;
                    $model->title = trim($html['title']->text());
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
        // パラメータから日付を取得
        if(count($params) > 4){
            for($i = 4; $i < count($params); $i ++){
                $selector = $params[$i];
                // ページが作成されている場合にはページ内の項目を登録
                $page = $data["page"];
                if($page->page_id > 0){
                    // トランザクションの開始
                    $connection = Vizualizer_Database_Factory::begin("content");

                    // ページ項目モデルを生成
                    $loader = new Vizualizer_Plugin("Content");
                    $model = $loader->loadModel("PageItem");

                    // ページ内の項目全体に削除フラグを立てる
                    $items = $model->findAllByPageId($page->page_id);
                    foreach($items as $item){
                        $item->deleted = 1;
                        $item->save();
                    }

                    try {
                        $html = $data["content"];
                        $items = $html[$selector];

                        foreach($items as $item){
                            $model = $loader->loadModel("PageItem");
                            $model->findByPageItem($page->page_id, $selector, pq($item)->text());
                            if(!($model->page_item_id > 0)){
                                $model->page_id = $page->page_id;
                                $model->item_selector = $selector;
                                $model->item_value = pq($item)->text();
                                $model->created = 1;
                            }
                            $model->deleted = 0;
                            $model->save();
                        }

                        // エラーが無かった場合、処理をコミットする。
                        Vizualizer_Database_Factory::commit($connection);
                    } catch (Exception $e) {
                        Vizualizer_Database_Factory::rollback($connection);
                        throw new Vizualizer_Exception_Database($e);
                    }
                }
            }
        }
        return $data;
    }
}
