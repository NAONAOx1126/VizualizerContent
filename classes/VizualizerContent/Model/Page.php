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
 * コンテンツページのモデルです。
 *
 * @package VizualizerContent
 * @author Naohisa Minagawa <info@vizualizer.jp>
 */
class VizualizerContent_Model_Page extends Vizualizer_Plugin_Model
{

    /**
     * コンストラクタ
     *
     * @param $values モデルに初期設定する値
     */
    public function __construct($values = array())
    {
        $loader = new Vizualizer_Plugin("content");
        parent::__construct($loader->loadTable("Pages"), $values);
    }

    /**
     * 主キーでデータを取得する。
     *
     * @param $page_id ページID
     */
    public function findByPrimaryKey($page_id)
    {
        $this->findBy(array("page_id" => $page_id));
    }

    /**
     * URLでデータを取得する。
     *
     * @param $page_url ページURL
     */
    public function findByPageUrl($page_url)
    {
        $this->findBy(array("page_url" => $page_url));
    }

    /**
     * ページの要素を取得する
     *
     * @return ページ要素
     */
    public function items()
    {
        $loader = new Vizualizer_Plugin("content");
        $pageItem = $loader->loadModel("PageItem");
        $pageItems = $pageItem->findAllByPageId($this->page_id);
        return $pageItems;
    }
}
