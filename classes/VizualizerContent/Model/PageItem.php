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
 * コンテンツページ要素のモデルです。
 *
 * @package VizualizerContent
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class VizualizerContent_Model_PageItem extends Vizualizer_Plugin_Model
{

    /**
     * コンストラクタ
     *
     * @param $values モデルに初期設定する値
     */
    public function __construct($values = array())
    {
        $loader = new Vizualizer_Plugin("content");
        parent::__construct($loader->loadTable("PageItems"), $values);
    }

    /**
     * 主キーでデータを取得する。
     *
     * @param $page_item_id ページ要素ID
     */
    public function findByPrimaryKey($page_item_id)
    {
        $this->findBy(array("page_item_id" => $page_item_id));
    }

    /**
     * ページIDでデータを取得する。
     *
     * @param $page_id ページID
     */
    public function findByPageId($page_id)
    {
        $this->findBy(array("page_id" => $page_id));
    }

    /**
     * ページを取得する
     *
     * @return ページ
     */
    public function page()
    {
        $loader = new Vizualizer_Plugin("content");
        $pageItem = $loader->loadModel("Page");
        $pageItems = $pageItem->findAllByPrimaryKey($this->page_id);
        return $pageItems;
    }
}
