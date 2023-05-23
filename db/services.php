<?php
/**
 * Services definition.
 *
 * @package qtype_minispeak
 * @author  Justin Hunt - poodll.com
 */

$functions = array(

        'qtype_minispeak_report_step_grade' => array(
                'classname'   => 'qtype_minispeak_external',
                'methodname'  => 'report_step_grade',
                'description' => 'Reports the grade of a step',
                'capabilities'=> 'qtype/minispeak:view',
                'type'        => 'write',
                'ajax'        => true,
        ),

        'qtype_minispeak_check_by_phonetic' => array(
                'classname'   => 'qtype_minispeak_external',
                'methodname'  => 'check_by_phonetic',
                'description' => 'compares a spoken phrase to a correct phrase by phoneme' ,
                'capabilities'=> 'qtype/minispeak:view',
                'type'        => 'read',
                'ajax'        => true,
        ),

        'qtype_minispeak_compare_passage_to_transcript' => array(
            'classname'   => 'qtype_minispeak_external',
            'methodname'  => 'compare_passage_to_transcript',
            'description' => 'compares a spoken phrase to a correct phrase' ,
            'capabilities'=> 'qtype/minispeak:view',
            'type'        => 'read',
            'ajax'        => true,
        )

);