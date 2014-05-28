CREATE TABLE IF NOT EXISTS `content_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ページID',
  `page_url` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'ページURL',
  `page_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'ページタイトル',
  `page_description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'ページ説明',
  `create_time` datetime NOT NULL COMMENT 'データ作成日時',
  `update_time` datetime NOT NULL COMMENT 'データ最終更新日時',
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `page_url` (`page_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='ページテーブル';
