<?php
class Helper_Rss {
	public static function parse(array $data) {
		$result = '';
		foreach($data as $item) {
			$result .= '<item>
		<title>'.$item['title'].'</title>
		<link>'.$item['permalink'].'</link>
		<pubDate>'.date('r', $item['timestamp']).'</pubDate>
		<dc:creator>'.$item['creator'].'</dc:creator>
		<guid isPermaLink="false">'.$item['permalink'].'</guid>
		<description><![CDATA['.$item['body'].']]></description>
		<content:encoded><![CDATA['.$item['body'].']]></content:encoded>
	</item>';
		}
		return $result;
	}
}