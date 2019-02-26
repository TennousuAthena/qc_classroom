<?php
/**
 * Created by Lane
 * User: lane
 * Date: 16/3/25
 * Time: 下午6:38
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core;

class Log
{
    private static $fileResource = null;

    private static function getInstance()
    {
        if (is_null(self::$fileResource)) {
            self::$fileResource = fopen(MEEPO_PS_LOG_PATH, 'a');
        }
    }

    public static function write($msg, $type = 'INFO')
    {
        self::getInstance();
        $type = strtoupper($type);
        if (!in_array($type, array('INFO', 'ERROR', 'FATAL', 'WARNING', "TEST"))) {
            exit('Log type no match');
        }
        $msg = '[' . $type . '][' . date('Y-m-d H:i:s') . '][' . getmypid() . ']' . $msg . "\n";
        fwrite(self::$fileResource, $msg);
        if (MEEPO_PS_DEBUG) {
            echo $msg;
        }
        if ($type === 'FATAL') {
            exit;
        }
    }
}