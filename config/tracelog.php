<?php

return [
    // no need to set
    'id' => '',

    'trace_id_header_key' => 'X-Trace-Id',

    // do not record the value of the header specified key to the log
    'filter_ignored_header_keys' => 'authorization',

    // no logging
    'filter_ignored_keys' => '', // eg: 'password,password_confirmation'

    // replace all data with *
    'filter_hide_keys' => '',

    // hide part of the data, show only the first 20% and last 20% of the data, replace the rest with *
    'filter_half_hide_keys' => '',  // eg: 'client_id,client_secret'

    // additional data to be logged
    'additional_fields' => [
        // 'user_id' => function () {
        //    return auth()->id();
        // },
        // 'tag' => 'test',
    ],
];
