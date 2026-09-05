<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_neko_auto_avatar {

	function avatar($param) {
		global $_G;

		// 本方法内部探测用的 avatar() 调用：直接走核心原逻辑
		if(!empty($_G['neko_av_probe'])) {
			return '';
		}

		$args = $param['param'] ?? [];
		$uid = abs(intval($args[0] ?? 0));
		if(!$uid) {
			return '';
		}

		$member = table_common_member::t()->fetch($uid);
		if(!$member || !self::core_shows_noavatar($uid, $args)) {
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

	// 核心是否会给该用户显示默认头像：用核心 avatar() 的返回结果判断
	static function core_shows_noavatar($uid, $args) {
		global $_G;
		static $cache = [];
		if(isset($cache[$uid])) {
			return $cache[$uid];
		}
		if(!empty($_G['setting']['ftp']['on']) && $_G['setting']['ftp']['on'] == 2 && $_G['setting']['oss']['oss_avatar']) {
			// ponytail: OSS 模式核心直接返回远程地址、不校验存在性，只能信 avatarstatus
			$member = table_common_member::t()->fetch($uid);
			return $cache[$uid] = !$member || empty($member['avatarstatus']);
		}
		$_G['neko_av_probe'] = 1;
		// ponytail: DIY block（block_thread 等）会以 avatarapi=true 调用 avatar()，核心返回
		// /avatar/?uid= 路由地址，既不含 noavatar 也不含 avatar.php?uid=，探测会误判为有头像。
		// 探测时固定用代理地址格式（不用调用方传入的 ucenterurl/avatarapi），保证结果可判定
		$real = (string)avatar($uid, $args[1] ?? 'middle', $args[2] ?? 0);
		unset($_G['neko_av_probe']);
		$replace = str_contains($real, 'noavatar');
		if(!$replace && str_contains($real, 'avatar.php?uid=')) {
			// 动态代理地址不含存在性信息：优先查本机 UC 头像文件，目录不可见再问 UC API
			$uid9 = sprintf('%09d', $uid);
			$filepath = substr($uid9, 0, 3).'/'.substr($uid9, 3, 2).'/'.substr($uid9, 5, 2).'/'.substr($uid9, -2).'_avatar_middle.jpg';
			$ucpath = (string)parse_url($real, PHP_URL_PATH);
			$ucdir = $ucpath ? trim(dirname($ucpath), '/\\').'/' : '';
			$ucdatafile = DISCUZ_ROOT.$ucdir.'data/avatar/'.$filepath;
			if(is_dir(DISCUZ_ROOT.$ucdir.'data/avatar')) {
				$replace = !file_exists($ucdatafile);
			} else {
				loaducenter();
				// ponytail: 远程 UCenter 时每 uid 每请求一次 HTTP 查询；量大可升级为持久缓存
				$replace = !function_exists('uc_check_avatar') || !uc_check_avatar($uid, 'middle');
			}
		}
		return $cache[$uid] = $replace;
	}
}
