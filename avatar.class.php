<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_neko_auto_avatar {

	function avatar($param) {
		global $_G;

		$args = $param['param'] ?? [];
		$uid = abs(intval($args[0] ?? 0));
		if(!$uid) {
			return '';
		}

		$member = table_common_member::t()->fetch($uid);
		if(!$member || !empty($member['avatarstatus'])) {
			return '';
		}

		$config = $_G['cache']['plugin']['neko_auto_avatar'] ?? [];
		$prefix = (string)($config['prefix'] ?? 'https://rca.mcneko.com/avatar.svg?seed=');
		$suffix = (string)($config['suffix'] ?? '');
		$seedType = $config['seed_type'] ?? 'uid';

		switch($seedType) {
			case 'username':
				$seed = (string)$member['username'];
				break;
			case 'regdate':
				$seed = (string)$member['regdate'];
				break;
			case 'email_base64':
				$seed = base64_encode((string)$member['email']);
				break;
			default:
				$seed = (string)$uid;
		}

		$url = $prefix.rawurlencode($seed).$suffix;
		$returnsrc = $args[2] ?? 0;
		$class = $args[6] ?? '';
		$extra = $args[7] ?? '';
		$src = !empty($args[10]) ? 'data-src' : 'src';
		$img = '<img '.$src.'="'.htmlspecialchars($url, ENT_QUOTES, CHARSET).'" class="'.($args[10] ? '_avt' : '').($class ? ' '.htmlspecialchars($class, ENT_QUOTES, CHARSET) : '').'"'.($extra ? ' '.trim($extra) : '').'>';

		$_G['hookavatar'] = $returnsrc ? $url : $img;
		return '';
	}
}
