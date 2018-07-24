<?php
return [    
    'hyperframework' => [
        'app_root_namespace' => 'HyperswooleTest',
        'web.csrf_protection.enable' => false,
        'web.initialize_global_post_data' => true,
        'db.operation_profiler.enable_logger' => false,
        'db.operation_profiler.profile_handler_class' => 'Shoufuyou\Util\DbProfileHandler',
        'error_handler.class' => 'Shoufuyou\Util\ErrorCashHandler',
        'web.request_engine_class'  => 'Hyperswoole\Web\RequestEngine',
        'web.response_engine_class' => 'Hyperswoole\Web\ResponseEngine',
        'db.connection_class'       => 'Hyperswoole\Db\DbConnection',
        'logging.handler.class'     => 'Hyperswoole\Logging\FileLogHandler',
    ]
];