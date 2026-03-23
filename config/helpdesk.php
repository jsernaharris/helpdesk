<?php

return [
    'ticket_prefixes' => [
        'incident' => 'INC',
        'service_request' => 'SR',
        'problem' => 'PRB',
        'change' => 'CHG',
    ],

    'auto_close_resolved_after_days' => 3,

    'default_timezone' => 'America/Chicago',
    'default_priority' => 'medium',
    'default_ticket_type' => 'incident',

    'email' => [
        'fetch_interval_seconds' => 60,
        'max_attachment_size_mb' => 25,
    ],

    'sla' => [
        'warning_threshold_percent' => 75,
    ],
];
