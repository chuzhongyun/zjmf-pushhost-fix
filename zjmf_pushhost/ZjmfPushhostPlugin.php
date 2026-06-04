<?php
namespace addons\zjmf_pushhost;

use app\admin\lib\Plugin;
use think\Db;


class ZjmfPushhostPlugin extends Plugin
{
    // 插件基本信息
    public $info = array(
        'name' => 'ZjmfPushhost',  // 插件英文名，改成你的插件英文就行了
        'title' => '财务无限同步下游修复',
        'description' => '财务无限同步下游（pushhostinfo）修复',
        'status' => 1,  // 状态
        'author' => '<a href="http://www.miniduo.cn">迷你哆云</a>',  // 开发者
        'version' => '1.0',  // 版本号
        'module' => 'addons',  // 插件模块
    );

    // 插件安装
    public function install()
    {
        return true;
    }

    // 插件卸载
    public function uninstall()
    {
        return true;
    }

    public function afterCron() {
        \think\Db::name('zjmf_pushhost')
            ->where('status', 0)
            ->where('time', '<', time() - 300 * 10)   // 最后处理时间在 5 分钟前
            ->update(['num' => 10]);
    }
}
