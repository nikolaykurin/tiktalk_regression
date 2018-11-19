<?php

return [

    'DEBUG' => env('TRAINING_DEBUG', false),
    'TEST_PERCENT' => env('TEST_PERCENT', 100),

    'CFG_WAVFILE_EXTENSION' => env('CFG_WAVFILE_EXTENSION', 'raw'),
    'CFG_WAVFILE_TYPE' => env('CFG_WAVFILE_TYPE', 'raw'),
    'CFG_N_TIED_STATES' => env('CFG_N_TIED_STATES', 200),
    'DEC_CFG_NPART' => env('DEC_CFG_NPART', 4),
    'CFG_CD_TRAIN' => env('CFG_CD_TRAIN', 'yes'),
    'CFG_FORCEDALIGN' => env('CFG_FORCEDALIGN', 'no'),
    'CFG_VTLN' => env('CFG_VTLN', 'no'),

];