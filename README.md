# neko_auto_avatar

Discuz! X5.0 插件：为没有上传头像的用户自动使用外部生成的头像地址。

## 功能

- 用户没有头像（`avatarstatus` 为 0）时，头像地址自动替换为可配置的外部头像服务。
- 用户上传真实头像后，自动恢复显示原头像。
- 后台可配置头像地址开头，默认 `https://rca.mcneko.com/avatar.svg?seed=`。
- 后台可配置头像地址结尾，留空则不拼接。
- 中间值（seed）可选：
  - UID
  - 用户名
  - 注册时间戳
  - Base64 编码邮箱
- 中间值经过 URL 编码后拼接，保证地址合法。
- 同时支持桌面版和手机版模板。

## 安装

1. 将 `neko_auto_avatar` 文件夹放入 Discuz! 的 `source/plugin/` 目录。
2. 在管理后台进入“应用/插件”，导入 `discuz_plugin_neko_auto_avatar.json`。
3. 启用插件并打开插件设置。
4. 填写头像地址开头、按需填写地址结尾，并选择中间值。
5. 修改设置后更新 Discuz! 缓存。

## 说明

- 插件通过 Discuz! 的 `avatar()` 函数钩子（桌面版 type 11、手机版 type 28）工作，不修改核心文件。
- 只有真正没有头像的用户会被替换；已上传头像的用户不受影响。
- 中间值应选择不会变化的字段（如 UID、注册时间戳），否则更换用户名或邮箱后头像会变化。

## 环境要求

- Discuz! X5.0
- PHP 8.0 或更高版本

## 开源协议

本项目使用 MIT License，详见 [LICENSE](LICENSE)。
