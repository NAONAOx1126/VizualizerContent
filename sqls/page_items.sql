CREATE TABLE IF NOT EXISTS `content_page_items` (
  `page_item_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ページ項目ID',
  `page_id` int(11) NOT NULL COMMENT 'ページID',
  `item_selector` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT '項目セレクタ',
  `item_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '値',
  `created` tinyint(1) NOT NULL COMMENT '作成フラグ',
  `updated` tinyint(1) NOT NULL COMMENT '更新フラグ',
  `create_time` datetime NOT NULL COMMENT 'データ作成日時',
  `update_time` datetime NOT NULL COMMENT 'データ最終更新日時',
  PRIMARY KEY (`page_item_id`),
  UNIQUE KEY `page_id_2` (`page_id`,`item_selector`,`item_value`),
  KEY `page_id` (`page_id`),
  KEY `item_selector` (`item_selector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='ページ項目テーブル';
