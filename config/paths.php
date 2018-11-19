<?php

return [

    'config' => 'sphinx4.config.xml',

    'tool_entry_point' => 'import_tool/index.php',
    'files' => 'files',
    'pocketsphinx' => '/usr/local/share/pocketsphinx',
    'sphinxtrain' => [
        'file' => '/usr/bin/sphinxtrain',
        'folder' => env('TRAIN_PATH', '/usr/lib/sphinxtrain')
    ],
    'sphinx_lm_convert' => '/usr/local/bin/sphinx_lm_convert',
    'cmu_language_toolkit' => [
        'text2wfreq' => '/usr/bin/text2wfreq',
        'text2idngram' => '/usr/bin/text2idngram',
        'idngram2lm' => '/usr/bin/idngram2lm'
    ]

];