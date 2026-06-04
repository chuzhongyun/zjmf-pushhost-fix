<?php
namespace addons\zjmf_pushhost;

use app\admin\lib\Plugin;
use think\Db;


class ZjmfPushhostPlugin extends Plugin
{
    // 插件基本信息
    public $info = array(
        'name' => 'ZjmfPushhost',
        'title' => '财务无限同步下游修复',
        'description' => '修复 Cron.php hostInfo() 缺少 id/num 字段导致的下游无限同步问题',
        'status' => 1,
        'author' => '<a href="https://github.com/chuzhongyun/zjmf-pushhost-fix">zjmf-pushhost-fix</a>',
        'version' => '2.0',
        'module' => 'addons',
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

    /**
     * afterCron 钩子：每次定时任务完成后执行
     * 兜底清理：将长时间未处理成功(status=0)且超过重试窗口的记录标记为跳过
     * time < now - 3000 表示连续 10 个周期(50分钟)未被成功更新
     */
    public function afterCron()
    {
        \think\Db::name('zjmf_pushhost')
            ->where('status', 0)
            ->where('time', '<', time() - 300 * 10)
            ->update(['num' => 10]);
    }

    /**
     * after_five_minute_cron 钩子：五分钟定时任务完成后执行
     * 在 hostInfo() 执行完毕后立即清理，缩短兜底窗口
     */
    public function afterFiveMinuteCron()
    {
        \think\Db::name('zjmf_pushhost')
            ->where('status', 0)
            ->where('time', '<', time() - 300 * 10)
            ->update(['num' => 10]);
    }
}