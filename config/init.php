<?php
return [    
    'hyperframework' => [
        'app_root_namespace' => 'HyperswooleTest',
        'web.csrf_protection.enable' => false,
        'web.initialize_global_post_data' => true,
        'db.operation_profiler.enable_logger' => false,
        'db.operation_profiler.profile_handler_class' => 'Shoufuyou\Util\DbProfileHandler',
        'web.request_engine_class'  => 'Hyperswoole\Web\RequestProxy',
        'web.response_engine_class' => 'Hyperswoole\Web\ResponseProxy',
        'db.connection_class'       => 'Hyperswoole\Db\CoDbConnection',
        'logging.handler.class'     => 'Hyperswoole\Logging\FileLogHandler',
        'error_handler.class'       => 'Hyperswoole\Web\ErrorHandler',
    ]
];