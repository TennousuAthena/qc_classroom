<?php
/**
 * MeepoPS的入口文件
 * Created by Lane
 * User: lane
 * Date: 16/3/23
 * Time: 下午2:12
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */

namespace MeepoPS;

use MeepoPS\Core\MeepoPS;

//MeepoPS根目录
define('MEEPO_PS_ROOT_PATH', dirname(__FILE__) . '/');

//载入MeepoPS配置文件
require_once MEEPO_PS_ROOT_PATH . '/Core/Config.php';

//环境检测
require_once MEEPO_PS_ROOT_PATH . '/Core/CheckEnv.php';

//载入MeepoPS核心文件
require_once MEEPO_PS_ROOT_PATH . '/Core/Init.php';

//启动MeepoPS
function runMeepoPS()
{
    MeepoPS::runMeepoPS();
}