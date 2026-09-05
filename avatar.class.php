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
		if(!$member || self::has_avatar($uid, $member)) {
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

	// 判断用户是否已有真实头像：仅当核心会回退到 noavatar 时才返回 false
	static function has_avatar($uid, $member) {
		global $_G;
		static $avtexist = [];
		if(isset($avtexist[$uid])) {
			return $avtexist[$uid];
		}
		$exists = !empty($member['avatarstatus']);
		if(!$exists) {
			if(!empty($_G['setting']['ftp']['on']) && $_G['setting']['ftp']['on'] == 2 && $_G['setting']['oss']['oss_avatar']) {
				// ponytail: OSS 头像无法低成本验证存在性，沿用 avatarstatus 判断
				$exists = false;
			} elseif(!empty($_G['setting']['avatarmethod'])) {
				// 静态头像模式：与核心 function_core.php avatar() 相同的路径与判断
				$uid9 = sprintf('%09d', $uid);
				$filepath = substr($uid9, 0, 3).'/'.substr($uid9, 3, 2).'/'.substr($uid9, 5, 2).'/'.substr($uid9, -2).'_avatar_middle.jpg';
				$exists = file_exists(DISCUZ_ROOT.$_G['setting']['avatarpath'].$filepath);
			} elseif(function_exists('uc_check_avatar')) {
				// UCenter 动态模式：向 UC 服务器查询头像是否存在
				// ponytail: 每请求每个 uid 一次 HTTP 查询；头像多的页面可升级为持久缓存
				$exists = (bool)uc_check_avatar($uid, 'middle');
			}
		}
		return $avtexist[$uid] = $exists;
	}
}
