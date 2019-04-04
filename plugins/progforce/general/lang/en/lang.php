<?php return [
    'plugin' => [
        'name' => 'General',
        'description' => '',
    ],
    'languageconfigurations' => [
        'menu_item' => 'Language Configurations',
        'id' => 'ID',
        'language' => 'Language',
        'path_to_images' => 'Path to images',
        'path_to_recordings' => 'Path to recordings',
        'table' => 'Table name'
    ],
    'patientprofiles' => [
        'menu_item' => 'Patient Profiles',
        'id' => 'ID',
        'user' => 'User',
        'first_name' => 'First Name',
        'slp' => 'SLP',
        'slp_first_name' => 'SLP Name',
        'age' => 'Age',
        'country' => 'Country',
        'language' => 'Country',
    ],
    'words' => [
        'menu_item' => 'Words en_us',
        'menu_item_he_il' => 'Words he_il',
        'id' => 'ID',
        'language' => 'Language',
        'word' => 'Word',
        'word_id' => 'Word ID',
        'sound' => 'Sound',
        'phoneme' => 'Phoneme',
        'number_of_syllables' => 'Number of Syllables',
        'intonation' => 'Intonation',
        'location_within_word' => 'Location within Word',
        'segment_location_within_phoneme' => 'Segment location within Phoneme',
        'complexity' => 'Complexity',
        'part_of_speech' => 'Part of Speech',
        'transcription1' => 'Transcription 1',
    ],
    'prerecordedwords' => [
        'menu_item' => 'Prerecorded Words',
        'id' => 'ID',
        'asset' => 'Asset',
        'asset_id' => 'Asset ID',
        'text' => 'Text',
        'import_export' => [
            'open_import_form' => 'Open Import Form',
            'id' => 'ID',
            'asset_id' => 'Asset ID',
            'text' => 'Text'
        ]
    ],
    'complexities' => [
        'menu_item' => 'Complexities',
        'id' => 'ID',
        'description' => 'Description',
    ],
    'countries' => [
        'menu_item' => 'Countries',
        'id' => 'ID',
        'code' => 'Code',
        'description' => 'Description',
    ],
    'partsofspeech' => [
        'menu_item' => 'Location Within Word',
        'id' => 'ID',
        'description' => 'Description',
    ],
    'patienttreatmentplans' => [
        'menu_item' => 'Patient Treatment Plans',
        'id' => 'ID',
        'user' => 'Patient',
        'protocol_sequence' => 'Protocol Sequence',
        'protocol_status' => 'Protocol Status',
        'sound' => 'Sound',
        'is_multisound' => 'Words with Multiple Sounds'
    ],
    'treatmentphases' => [
        'menu_item' => 'Treatment Phases',
        'id' => 'ID',
        'description' => 'Description',
        'number_of_syllables' => 'Number of Syllables',
        'intonation' => 'Intonation',
        'location_within_word' => 'Location within Word',
        'segment_location_within_phoneme' => 'Segment location within Phoneme',
        'complexity' => 'Complexity',
        'part_of_speech' => 'Part of Speech',
    ],
    'treatmentstatuses' => [
        'menu_item' => 'Treatment Statuses',
        'id' => 'ID',
        'description' => 'Description',
    ],
    'gamemodes' => [
        'menu_item' => 'Game modes',
        'name' => 'Game mode'
    ],
    'registereddevices' => [
        'menu_item' => 'Registered Devices',
        'id' => 'ID',
        'device_id' => 'Mixed ID'
    ],
    'scoringalgorithms' => [
        'menu_item' => 'Scoring Algorithms',
        'id' => 'ID',
        'field' => 'Field',
        'value' => 'Value'
    ],
    'logs' => [
        'menu_item' => 'Logs',
        'id' => 'ID',
        'user_id' => 'User',
        'user' => 'User',
        'datetime' => 'Date & Time',
        'action' => 'Action',
        'data' => 'Data'
    ],
    'parents' => [
        'menu_item' => 'Parents',
        'id' => 'ID',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password'
    ],
    'sessions' => [
        'menu_item' => 'Sessions',
        'id' => 'ID',
        'user_id' => 'Patient',
        'datetime_start' => 'Start',
        'datetime_end' => 'End'
    ],
    'progforce' => [
        'general::lang' => [
            'slpprofiles' => [
                'user' => 'User',
                'user_name' => 'User',
            ],
            'general' => [
                'gamemodes' => 'Game modes',
            ],
        ],
    ],
];
