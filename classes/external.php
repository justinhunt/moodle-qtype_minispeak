<?php
/**
 * External.
 *
 * @package qtype_minispeak
 * @author  Justin Hunt - poodll.com
 */


global $CFG;
require_once($CFG->libdir . '/externallib.php');

use qtype_minispeak\utils;
use qtype_minispeak\constants;
use qtype_minispeak\diff;
use qtype_minispeak\alphabetconverter;
use qtype_minispeak\local\itemtype\item;

use external_api;
use external_function_parameters;
use external_value;
use qtype_minispeak\JWT;

/**
 * External class.
 *
 * @package qtype_minispeak
 * @author  Justin Hunt - poodll.com
 */
class qtype_minispeak_external extends external_api {

    public static function check_by_phonetic_parameters(){
        return new external_function_parameters(
                 array('spoken' => new external_value(PARAM_TEXT, 'The spoken phrase'),
                       'correct' => new external_value(PARAM_TEXT, 'The correct phrase'),
                       'phonetic' => new external_value(PARAM_TEXT, 'The correct phonetic'),
                       'language' => new external_value(PARAM_TEXT, 'The language eg en-US'),
                       'region' => new external_value(PARAM_TEXT, 'The region'),
                       'cmid' => new external_value(PARAM_INT, 'The cmid'),
                 )
        );

    }
    public static function check_by_phonetic($spoken, $correct, $phonetic, $language,$region, $cmid){
        $segmented = true;
        $shortlang = utils::fetch_short_lang($language);
        switch($language){
            case constants::M_LANG_JAJP:

                //find digits in original passage, and convert number words to digits in the target passage
                //this works but segmented digits are a bit messed up, not sure its worthwhile. more testing needed
                $spoken=alphabetconverter::words_to_suji_convert($phonetic,$spoken);
                break;
            case constants::M_LANG_ENUS:
            case constants::M_LANG_ENAB:
            case constants::M_LANG_ENAU:
            case constants::M_LANG_ENGB:
            case constants::M_LANG_ENIE:
            case constants::M_LANG_ENIN:
            case constants::M_LANG_ENNZ:
            case constants::M_LANG_ENWL:
            case constants::M_LANG_ENZA:
                //find digits in original passage, and convert number words to digits in the target passage
                $spoken=alphabetconverter::words_to_numbers_convert($correct,$spoken,$shortlang);
                break;
            case constants::M_LANG_DEDE:
            case constants::M_LANG_DECH:
                //find eszetts in original passage, and convert ss words to eszetts in the target passage
                $spoken=alphabetconverter::ss_to_eszett_convert($correct,$spoken);
                break;
        }
        list($spokenphonetic) = utils::fetch_phones_and_segments($spoken,$language,$region,$segmented);
        $similar_percent = 0;

        //if our convert_to_phonetic returned false(error) then its hopeless, return 0
        if($spokenphonetic===false){
            return 0;
        }

        //if one of our phonetics is just empty, it is also hopeless
        if(empty($spokenphonetic) || empty($phonetic)){
            return 0;
        }

        //similar_percent calc'd by reference but multibyte is weird
        if($language!==constants::M_LANG_JAJP) {
            similar_text($phonetic, $spokenphonetic, $similar_percent);
        }else{
            $similar_percent = $phonetic == $spokenphonetic ?100:0;
        }
        return round($similar_percent,0);

    }

    public static function check_by_phonetic_returns(){
        return new external_value(PARAM_INT,'how close is spoken to correct, 0 - 100');
    }


    public static function report_step_grade_parameters() {
        return new external_function_parameters([
                'cmid' => new external_value(PARAM_INT),
                'qubaid' => new external_value(PARAM_SEQUENCE),
                'step' => new external_value(PARAM_TEXT),
                'store' => new external_value(PARAM_BOOL)
        ]);
    }

    public static function report_step_grade($cmid, $qubaid, $step, $store) {
        global $CFG;
        require_once($CFG->dirroot . '/question/engine/lib.php');
        $params = self::validate_parameters(self::report_step_grade_parameters(), [
            'cmid' => $cmid, 'qubaid' => $qubaid, 'step' => $step, 'store' => $store,
        ]);
        list($qubaid, $slot) = explode(',', $params['qubaid']);

        if (!empty($qubaid) && !empty($slot)) {
            $quba = question_engine::load_questions_usage_by_activity($qubaid);
            $qa = $quba->get_question_attempt($slot);
            if ($secret = $qa->get_last_qt_var(constants::TOKENKEYPRIVATE)) {
                $token = JWT::encode($step, $secret, 'HS256');
                $response['token'] = $token;
                if (!empty($params['store'])) {
                    $simulateddata = $quba->prepare_simulated_post_data([$slot => ['payload' => $token]]);
                    $quba->process_all_actions(time(), $simulateddata);
                    question_engine::save_questions_usage_by_activity($quba);
                    $response['newsequence'] = $quba->get_question_attempt($slot)->get_sequence_check_count();
                }
                return $response;
            }
        }

        return [];
    }
    public static function report_step_grade_returns() {
        return new external_single_structure([
            'token' => new external_value(PARAM_RAW, 'token', VALUE_DEFAULT),
            'newsequence' => new external_value(PARAM_RAW, 'squencecheck', VALUE_DEFAULT),
        ]);
    }


    public static function compare_passage_to_transcript_parameters(){
        return new external_function_parameters(
                array('transcript' => new external_value(PARAM_TEXT, 'The spoken phrase'),
                        'passage' => new external_value(PARAM_TEXT, 'The correct phrase'),
                        'language' => new external_value(PARAM_TEXT, 'The language eg en-US'),
                        'alternatives' => new external_value(PARAM_TEXT, 'list of alternatives',false,''),
                        'phonetic' => new external_value(PARAM_TEXT, 'phonetic reading',false,''),
                        'region' => new external_value(PARAM_TEXT, 'The region',false,'tokyo'),
                        'cmid' => new external_value(PARAM_INT, 'The cmid')
                )
        );

    }

    public static function compare_passage_to_transcript($transcript,$passage,$language,$alternatives,$phonetic,$region, $cmid) {
        global $DB;


        //Fetch phonetics and segments
        list($transcript_phonetic,$transcript) = utils::fetch_phones_and_segments($transcript,$language,$region);

        //EXPERIMENTAL
        $shortlang = utils::fetch_short_lang($language);
        switch ($shortlang){
            case 'en':
                //find digits in original passage, and convert number words to digits in the target passage
                $transcript=alphabetconverter::words_to_numbers_convert($passage,$transcript,$shortlang);
                break;
            case 'de':
                //find eszetts in original passage, and convert ss words to eszetts in the target passage
                $transcript=alphabetconverter::ss_to_eszett_convert($passage,$transcript );
                break;
            case 'ja':
                //find digits in original passage, and convert number words to digits in the target passage
                //this works but segmented digits are a bit messed up, not sure its worthwhile. more testing needed
                //from here and aigrade
                $transcript=alphabetconverter::words_to_suji_convert($passage,$transcript);
                break;


        }

        //If this is Japanese, and the passage has been segmented, we want to segment it into "words"
        /*
        if($language == constants::M_LANG_JAJP) {
            $transcript = utils::segment_japanese($transcript);
            $passage = utils::segment_japanese($passage);
            $segmented=true;
            $transcript_phonetic = utils::convert_to_phonetic($transcript,constants::M_LANG_JAJP,$region,$segmented);
        }else{
            $transcript_phonetic ='';
        }
        */

        //turn the passage and transcript into an array of words
        $passagebits = diff::fetchWordArray($passage);
        $alternatives = diff::fetchAlternativesArray($alternatives);
        $transcriptbits = diff::fetchWordArray($transcript);
        $transcriptphonetic_bits = diff::fetchWordArray($transcript_phonetic);
        $passagephonetic_bits = diff::fetchWordArray($phonetic);
        $wildcards = diff::fetchWildcardsArray($alternatives);

        //fetch sequences of transcript/passage matched words
        // then prepare an array of "differences"
        $passagecount = count($passagebits);
        $transcriptcount = count($transcriptbits);
        $sequences = diff::fetchSequences($passagebits, $transcriptbits, $alternatives, $language,
                $transcriptphonetic_bits , $passagephonetic_bits);
        //fetch diffs
        $debug=false;
        $diffs = diff::fetchDiffs($sequences, $passagecount, $transcriptcount, $debug);
        $diffs = diff::applyWildcards($diffs, $passagebits, $wildcards);


        //from the array of differences build error data, match data, markers, scores and metrics
        $errors = new \stdClass();
        $currentword = 0;

        //loop through diffs
        $results=[];
        foreach ($diffs as $diff) {
            $currentword++;
            $result = new \stdClass();
            $result->word = $passagebits[$currentword - 1];
            $result->wordnumber = $currentword;
            switch ($diff[0]) {
                case Diff::UNMATCHED:
                    //we collect error info so we can count and display them on passage

                    $result->matched =false;
                    break;

                case Diff::MATCHED:
                    $result->matched =true;
                    break;

                default:
                    //do nothing
                    //should never get here
            }
            $results[]=$result;
        }

        //finalise and serialise session errors
        $sessionresults = json_encode($results);

        return $sessionresults;

    }
    public static function compare_passage_to_transcript_returns() {
        return new external_value(PARAM_RAW);
    }
}
