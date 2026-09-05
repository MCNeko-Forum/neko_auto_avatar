<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once __DIR__.'/avatar.class.php';

// 手机版钩子与桌面版共用同一实现
class mobileplugin_neko_auto_avatar extends plugin_neko_auto_avatar {
}
