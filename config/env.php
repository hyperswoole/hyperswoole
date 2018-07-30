<?php
return [
    'shoufuyou' => [
        'app_domain'    => 'app-api.xinyongfei.cn', 
        'mobile_domain' => 'm.xinyongfei.cn',                                    
        'image_domain'  => 'img.xyfstatic.com',
        'static_domain' => 'test2-m.xinyongfei.cn'
    ],

    'hyperframework.web' => [
        'debugger.enable'   => false,                                               
        'error_view.enable' => true,
    ],

    'hyperframework' => [
        'db.operation_profiler.enable'      => true,                                  
        'db.operation_profiler.ignore_read' => true,                                        
    ],
    
    'shoufuyou.redis' => [
        'host'   => '39.106.113.243',
        'port'   => '6379',    
        'expire' => 3600,
        'pwd'    => '1hq234hq'
    ],

    'hyperframework.swoole' => [
        'ip'                  => '0.0.0.0',
        'port'                => 9501,
        'worker_num'          => 4,
        'daemonize'           => 1,
        'ssl_cert_file'       => '../data/server.crt',
        'ssl_key_file'        => '../data/server.key',
        'open_http2_protocol' => false,
    ],
];
