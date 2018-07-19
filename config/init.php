<?php
return [    
    'hyperframework' => [
        'app_root_namespace' => 'HyperswooleTest',
        'web.csrf_protection.enable' => false,
        'web.initialize_global_post_data' => true,
        'db.operation_profiler.enable_logger' => false,
        'db.operation_profiler.profile_handler_class' => 'Shoufuyou\Util\DbProfileHandler',
        'error_handler.class' => 'Shoufuyou\Util\ErrorCashHandler',
        'web.request_engine_class'  => 'Hyperswoole\Web\SwooleRequestEngine',
        'web.response_engine_class' => 'Hyperswoole\Web\SwooleResponseEngine'
    ]
];