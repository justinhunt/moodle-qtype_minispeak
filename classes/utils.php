<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Utils for minispeak plugin
 *
 * @package    qtype_minispeak
 * @copyright  2023 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_minispeak;

defined('MOODLE_INTERNAL') || die();

use \qtype_minispeak\constants;


/**
 * Functions used generally across this mod
 *
 * @package    qtype_minispeak
 * @copyright  2023 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils{

    //const CLOUDPOODLL = 'http://localhost/moodle';
    //const CLOUDPOODLL = 'https://vbox.poodll.com/cphost';
    const CLOUDPOODLL = 'https://cloud.poodll.com';

    //we need to consider legacy client side URLs and cloud hosted ones
    public static function make_audio_URL($filename, $contextid, $component, $filearea, $itemid){
        //we need to consider legacy client side URLs and cloud hosted ones
        if(strpos($filename,'http')===0){
            $ret = $filename;
        }else {
            $ret = \moodle_url::make_pluginfile_url($contextid, $component,
                $filearea,
                $itemid, '/',
                $filename);
        }
        return $ret;
    }


    /*
 * Do we need to build a language model for this passage?
 *
 */
    public static function needs_lang_model($moduleinstance, $passage) {
        $config = get_config(constants::M_COMPONENT);
        switch($config->awsregion){

            case 'capetown':
            case 'bahrain':
            case 'tokyo':
            case 'useast1':
            case 'dublin':
            case 'sydney':
            default:
                $shortlang = self::fetch_short_lang($moduleinstance->ttslanguage);
                return ($shortlang=='en' ||
                        $shortlang=='de' ||
                        $shortlang=='fr' ||
                        $shortlang=='ru' ||
                        $shortlang=='eu' ||
                        $shortlang=='pl' ||
                        $shortlang=='fi' ||
                        $shortlang=='it' ||
                        $shortlang=='pt' ||
                        $shortlang=='uk' ||
                        $shortlang=='hu' ||
                        $shortlang=='ro' ||
                        $shortlang=='es') && trim($passage)!=="";
        }
    }

    /*
     * Hash the passage and compare
     *
     */
    public static function fetch_passagehash($ttslanguage,$passage) {

        $cleanpassage = self::fetch_clean_passage($passage);

        //number or odd char converter
        $shortlang = self::fetch_short_lang($ttslanguage);
        if($shortlang=='en' || $shortlang=='de' ){
            //find numbers in the passage, and then replace those with words in the target text
            switch ($shortlang){
                case 'en':
                    $cleanpassage=alphabetconverter::numbers_to_words_convert($cleanpassage,$cleanpassage, $shortlang);
                    break;
                case 'de':
                    $cleanpassage=alphabetconverter::eszett_to_ss_convert($cleanpassage,$cleanpassage);
                    break;

            }
        }

        if(!empty($cleanpassage)) {
            return sha1($cleanpassage);
        }else{
            return false;
        }
    }

    public static function fetch_short_lang($longlang){
        if(\core_text::strlen($longlang)<=2){return $longlang;}
        if($longlang=="fil-PH"){return "fil";}
        $shortlang = substr($longlang,0,2);
        return $shortlang;
    }

    /*
     * Hash the passage and compare
     *
     */
    public static function fetch_clean_passage($passage) {
        $sentences = explode(PHP_EOL,$passage);
        $usesentences = [];
        //look out for display text sep. by pipe chars in string
        foreach($sentences as $sentence){
            $sentencebits = explode('|',$sentence);
            if(count($sentencebits)>1){
                $usesentences[] = trim($sentencebits[1]);
            }else{
                $usesentences[] = $sentence;
            }
        }
        $usepassage = implode(PHP_EOL, $usesentences);

        $cleantext = diff::cleanText($usepassage);
        if(!empty($cleantext)) {
            return $cleantext;
        }else{
            return false;
        }
    }


    /*
     * Build a language model for this text
     *
     */
    public static function fetch_lang_model($passage, $language, $region){
        $usepassage = self::fetch_clean_passage($passage);
        if($usepassage===false ){return false;}

        //get our 2 letter lang code
        $shortlang = self::fetch_short_lang($language);

        //find digits in original passage, and convert number words to digits in the target passage
        $usepassage=alphabetconverter::numbers_to_words_convert($usepassage,$usepassage, $shortlang);


        //other conversions
        switch ($shortlang){

            case 'de':
                //find eszetts in original passage, and convert ss words to eszetts in the target passage
                $params["passage"]=alphabetconverter::eszett_to_ss_convert($usepassage,$usepassage);
                break;

        }

        $conf= get_config(constants::M_COMPONENT);
        if (!empty($conf->apiuser) && !empty($conf->apisecret)) {
            $token = self::fetch_token($conf->apiuser, $conf->apisecret);
            //$token = self::fetch_token('russell', 'Password-123',true);

            if(empty($token)){
                return false;
            }
            $url = self::CLOUDPOODLL . "/webservice/rest/server.php";
            $params["wstoken"]=$token;
            $params["wsfunction"]='local_cpapi_generate_lang_model';
            $params["moodlewsrestformat"]='json';
            $params["passage"]=$usepassage;
            $params["language"]=$language;
            $params["region"]=$region;

            $resp = self::curl_fetch($url,$params);
            $respObj = json_decode($resp);
            $ret = new \stdClass();
            if(isset($respObj->returnCode)){
                $ret->success = $respObj->returnCode =='0' ? true : false;
                $ret->payload = $respObj->returnMessage;
            }else{
                $ret->success=false;
                $ret->payload = "unknown problem occurred";
            }
            return $ret;
        }else{
            return false;
        }
    }



    public static function update_step_grade($cmid,$stepdata){

        global $CFG, $USER, $DB;

        $message = '';
        $returndata=false;
        $result = true;

        //return_to_page -  just a placeholder function
        return [$result,$message,$returndata];
    }

    //JSON stringify functions will make objects(not arrays) if keys are not sequential
    //sometimes we seem to miss a step. Remedying that with this function prevents an all out disaster.
    // But we should not miss steps
    public static function remake_steps_as_array($stepsobject){
        if(is_array($stepsobject)) {
            return $stepsobject;
        }else{
            $steps = [];
            foreach ($stepsobject as $key => $value)
            {
                if(is_numeric($key)){
                    $key=intval($key);
                    $steps[$key] = $value;
                }

            }
            return $steps;
        }
    }

    public static function calculate_session_score($steps){
        $results = array_filter($steps, function($step){return $step->hasgrade;});
        $correctitems = 0;
        $totalitems = 0;
        foreach($results as $result){
            $correctitems += $result->correctitems;
            $totalitems += $result->totalitems;
        }
        $totalpercent = round(($correctitems/$totalitems)*100,0);
        return $totalpercent;
    }


    public static function create_new_attempt($courseid, $moduleid){
        global $DB,$USER;

        $newattempt = new \stdClass();
        $newattempt->courseid = $courseid;
        $newattempt->moduleid = $moduleid;
        $newattempt->status = constants::M_STATE_INCOMPLETE;
        $newattempt->userid = $USER->id;
        $newattempt->timecreated = time();
        $newattempt->timemodified = time();

        $newattempt->id = $DB->insert_record(constants::M_ATTEMPTSTABLE,$newattempt);
        return $newattempt;

    }

    //De accent and other processing so our auto transcript will match the passage
    public static function remove_accents_and_poormatchchars($text, $language){
        switch($language){
            case constants::M_LANG_UKUA:
                $ret = str_replace(
                    array("е́","о́","у́","а́","и́","я́","ю́","Е́","О́","У́","А́","И́","Я́","Ю́","“","”","'","́"),
                    array("е","о","у","а","и","я","ю","Е","О","У","А","И","Я","Ю","\"","\"","’",""),
                    $text
                );
                break;
            default:
                $ret = $text;
        }
        return $ret;
    }


    //are we willing and able to transcribe submissions?
    public static function can_transcribe($instance) {

        //we default to true
        //but it only takes one no ....
        $ret = true;

        //The regions that can transcribe
        switch($instance->region){
            default:
                $ret = true;
        }


        return $ret;
    }

    //see if this is truly json or some error
    public static function is_json($string) {
        if(!$string){return false;}
        if(empty($string)){return false;}
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    public static function curl_fetch($url,$postdata=false,$method='get')
    {
        global $CFG;

        require_once($CFG->libdir.'/filelib.php');
        $curl = new \curl();

        if($method=='get') {
            $result = $curl->get($url, $postdata);
        }else{
            $result = $curl->post($url, $postdata);
        }
        return $result;
    }

    //This is called from the settings page and we do not want to make calls out to cloud.poodll.com on settings
    //page load, for performance and stability issues. So if the cache is empty and/or no token, we just show a
    //"refresh token" links
    public static function fetch_token_for_display($apiuser,$apisecret){
       global $CFG;

       //First check that we have an API id and secret
        //refresh token
        $refresh = \html_writer::link($CFG->wwwroot . '/qtype/minispeak/refreshtoken.php',
                get_string('refreshtoken',constants::M_COMPONENT)) . '<br>';


        $message = '';
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        if(empty($apiuser)){
           $message .= get_string('noapiuser',constants::M_COMPONENT) . '<br>';
       }
        if(empty($apisecret)){
            $message .= get_string('noapisecret',constants::M_COMPONENT);
        }

        if(!empty($message)){
            return $refresh . $message;
        }

        //Fetch from cache and process the results and display
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        //if we have no token object the creds were wrong ... or something
        if(!($tokenobject)){
            $message = get_string('notokenincache',constants::M_COMPONENT);
            //if we have an object but its no good, creds werer wrong ..or something
        }elseif(!property_exists($tokenobject,'token') || empty($tokenobject->token)){
            $message = get_string('credentialsinvalid',constants::M_COMPONENT);
        //if we do not have subs, then we are on a very old token or something is wrong, just get out of here.
        }elseif(!property_exists($tokenobject,'subs')){
            $message = 'No subscriptions found at all';
        }
        if(!empty($message)){
            return $refresh . $message;
        }

        //we have enough info to display a report. Lets go.
        foreach ($tokenobject->subs as $sub){
            $sub->expiredate = date('d/m/Y',$sub->expiredate);
            $message .= get_string('displaysubs',constants::M_COMPONENT, $sub) . '<br>';
        }
        //Is app authorised
        if(in_array(constants::M_COMPONENT,$tokenobject->apps)){
            $message .= get_string('appauthorised',constants::M_COMPONENT) . '<br>';
        }else{
            $message .= get_string('appnotauthorised',constants::M_COMPONENT) . '<br>';
        }

        return $refresh . $message;

    }

    //We need a Poodll token to make all this recording and transcripts happen
    public static function fetch_token($apiuser, $apisecret, $force=false)
    {

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');
        $tokenuser = $cache->get('recentpoodlluser');
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        $now = time();

        //if we got a token and its less than expiry time
        // use the cached one
        if($tokenobject && $tokenuser && $tokenuser==$apiuser && !$force){
            if($tokenobject->validuntil == 0 || $tokenobject->validuntil > $now){
               // $hoursleft= ($tokenobject->validuntil-$now) / (60*60);
                return $tokenobject->token;
            }
        }

        // Send the request & save response to $resp
        $token_url = self::CLOUDPOODLL . "/local/cpapi/poodlltoken.php";
        $postdata = array(
            'username' => $apiuser,
            'password' => $apisecret,
            'service'=>'cloud_poodll'
        );
        $token_response = self::curl_fetch($token_url,$postdata);
        if ($token_response) {
            $resp_object = json_decode($token_response);
            if($resp_object && property_exists($resp_object,'token')) {
                $token = $resp_object->token;
                //store the expiry timestamp and adjust it for diffs between our server times
                if($resp_object->validuntil) {
                    $validuntil = $resp_object->validuntil - ($resp_object->poodlltime - $now);
                    //we refresh one hour out, to prevent any overlap
                    $validuntil = $validuntil - (1 * HOURSECS);
                }else{
                    $validuntil = 0;
                }

                $tillrefreshhoursleft= ($validuntil-$now) / (60*60);


                //cache the token
                $tokenobject = new \stdClass();
                $tokenobject->token = $token;
                $tokenobject->validuntil = $validuntil;
                $tokenobject->subs=false;
                $tokenobject->apps=false;
                $tokenobject->sites=false;
                if(property_exists($resp_object,'subs')){
                    $tokenobject->subs = $resp_object->subs;
                }
                if(property_exists($resp_object,'apps')){
                    $tokenobject->apps = $resp_object->apps;
                }
                if(property_exists($resp_object,'sites')){
                    $tokenobject->sites = $resp_object->sites;
                }

                $cache->set('recentpoodlltoken', $tokenobject);
                $cache->set('recentpoodlluser', $apiuser);

            }else{
                $token = '';
                if($resp_object && property_exists($resp_object,'error')) {
                    //ERROR = $resp_object->error
                }
            }
        }else{
            $token='';
        }
        return $token;
    }

    //check token and tokenobject(from cache)
    //return error message or blank if its all ok
    public static function fetch_token_error($token){
        global $CFG;

        //check token authenticated
        if(empty($token)) {
            $message = get_string('novalidcredentials', constants::M_COMPONENT,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            return $message;
        }

        // Fetch from cache and process the results and display.
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        //we should not get here if there is no token, but lets gracefully die, [v unlikely]
        if (!($tokenobject)) {
            $message = get_string('notokenincache', constants::M_COMPONENT);
            return $message;
        }

        //We have an object but its no good, creds were wrong ..or something. [v unlikely]
        if (!property_exists($tokenobject, 'token') || empty($tokenobject->token)) {
            $message = get_string('credentialsinvalid', constants::M_COMPONENT);
            return $message;
        }
        // if we do not have subs.
        if (!property_exists($tokenobject, 'subs')) {
            $message = get_string('nosubscriptions', constants::M_COMPONENT);
            return $message;
        }
        // Is app authorised?
        if (!property_exists($tokenobject, 'apps') || !in_array(constants::M_COMPONENT, $tokenobject->apps)) {
            $message = get_string('appnotauthorised', constants::M_COMPONENT);
            return $message;
        }

        //just return empty if there is no error.
        return '';
    }

    /*
     * Turn a passage with text "lines" into html "brs"
     *
     * @param String The passage of text to convert
     * @param String An optional pad on each replacement (needed for processing when marking up words as spans in passage)
     * @return String The converted passage of text
     */
    public static function lines_to_brs($passage,$seperator=''){
        //see https://stackoverflow.com/questions/5946114/how-to-replace-newline-or-r-n-with-br
        return str_replace("\r\n",$seperator . '<br>' . $seperator,$passage);
        //this is better but we can not pad the replacement and we need that
        //return nl2br($passage);
    }


    //take a json string of session errors/self-corrections, and count how many there are.
    public static function count_objects($items){
        $objects = json_decode($items);
        if($objects){
            $thecount = count(get_object_vars($objects));
        }else{
            $thecount=0;
        }
        return $thecount;
    }

     /**
     * Returns the link for the related activity
     * @return stdClass
     */
    public static function fetch_next_activity($activitylink) {
        global $DB;
        $ret = new \stdClass();
        $ret->url=false;
        $ret->label=false;
        if(!$activitylink){
            return $ret;
        }

        $module = $DB->get_record('course_modules', array('id' => $activitylink));
        if ($module) {
            $modname = $DB->get_field('modules', 'name', array('id' => $module->module));
            if ($modname) {
                $instancename = $DB->get_field($modname, 'name', array('id' => $module->instance));
                if ($instancename) {
                    $ret->url = new \moodle_url('/mod/'.$modname.'/view.php', array('id' => $activitylink));
                    $ret->label = get_string('activitylinkname',constants::M_COMPONENT, $instancename);
                }
            }
        }
        return $ret;
    }

  public static function get_region_options(){
      return array(
        "useast1" => get_string("useast1",constants::M_COMPONENT),
          "tokyo" => get_string("tokyo",constants::M_COMPONENT),
          "sydney" => get_string("sydney",constants::M_COMPONENT),
          "dublin" => get_string("dublin",constants::M_COMPONENT),
          "capetown" => get_string("capetown",constants::M_COMPONENT),
          "bahrain" => get_string("bahrain",constants::M_COMPONENT),
           "ottawa" => get_string("ottawa",constants::M_COMPONENT),
           "frankfurt" => get_string("frankfurt",constants::M_COMPONENT),
           "london" => get_string("london",constants::M_COMPONENT),
           "saopaulo" => get_string("saopaulo",constants::M_COMPONENT),
           "singapore" => get_string("singapore",constants::M_COMPONENT),
            "mumbai" => get_string("mumbai",constants::M_COMPONENT)
      );
  }



    public static function get_timelimit_options(){
        return array(
            0 => get_string("notimelimit",constants::M_COMPONENT),
            30 => get_string("xsecs",constants::M_COMPONENT,'30'),
            45 => get_string("xsecs",constants::M_COMPONENT,'45'),
            60 => get_string("onemin",constants::M_COMPONENT),
            90 => get_string("oneminxsecs",constants::M_COMPONENT,'30'),
            120 => get_string("xmins",constants::M_COMPONENT,'2'),
            150 => get_string("xminsecs",constants::M_COMPONENT,array('minutes'=>2,'seconds'=>30)),
            180 => get_string("xmins",constants::M_COMPONENT,'3')
        );
    }

    //Insert spaces in between segments in order to create "words"
    public static function segment_japanese($passage){
        $segments = \qtype_minispeak\jp\Analyzer::segment($passage);
        return implode(" ",$segments);
    }

    //convert a phrase or word to a series of phonetic characters that we can use to compare text/spoken
    //the segments will usually just return the phrase , but in japanese we want to segment into words
    public static function fetch_phones_and_segments($phrase, $language, $region='tokyo', $segmented=true){
        global $CFG;

        //first we check if the phrase is segmented with a pipe
        //if we have a pipe prompt = array[0] and response = array[1]
        $phrasebits = explode('|', $phrase);
        if (count($phrasebits) > 1) {
            $phrase = trim($phrasebits[1]);
        }

        switch($language){
            case constants::M_LANG_ENUS:
            case constants::M_LANG_ENAB:
            case constants::M_LANG_ENAU:
            case constants::M_LANG_ENNZ:
            case constants::M_LANG_ENZA:
            case constants::M_LANG_ENIN:
            case constants::M_LANG_ENIE:
            case constants::M_LANG_ENWL:
            case constants::M_LANG_ENGB:
                $phrasebits = explode(' ',$phrase);
                $phonebits=[];
                foreach($phrasebits as $phrasebit){
                    $phonebits[] = metaphone($phrasebit);
                }
                if($segmented) {
                    $phonetic = implode(' ', $phonebits);
                    $segments=$phrase;
                }else {
                    $phonetic = implode('', $phonebits);
                    $segments=$phrase;
                }
                $phones_and_segments = [$phonetic,$segments];
                //the resulting phonetic string will look like this: 0S IS A TK IT IS A KT WN TW 0T IS A MNK
                // but "one" and "won" result in diff phonetic strings and non english support is not there so
                //really we want to put an IPA database on services server and poll as we do for katakanify
                //see: https://github.com/open-dict-data/ipa-dict
                //and command line searchable dictionaries https://github.com/open-dsl-dict/ipa-dict-dsl based on those
                // gdcl :    https://github.com/dohliam/gdcl
                break;
            case constants::M_LANG_JAJP:

                //fetch katakana/hiragana if the JP
                $katakanify_url = utils::fetch_lang_server_url($region,'katakanify');

                //results look like this:

                /*
                    {
                        "status": true,
                        "message": "Katakanify complete.",
                        "data": {
                            "status": true,
                            "results": [
                                "元気な\t形容詞,*,ナ形容詞,ダ列基本連体形,元気だ,げんきな,代表表記:元気だ/げんきだ",
                                "男の子\t名詞,普通名詞,*,*,男の子,おとこのこ,代表表記:男の子/おとこのこ カテゴリ:人 ドメイン:家庭・暮らし",
                                "は\t助詞,副助詞,*,*,は,は,連語",
                                "いい\t動詞,*,子音動詞ワ行,基本連用形,いう,いい,連語",
                                "こ\t接尾辞,動詞性接尾辞,カ変動詞,未然形,くる,こ,連語",
                                "です\t判定詞,*,判定詞,デス列基本形,だ,です,連語",
                                "。\t特殊,句点,*,*,。,。,連語",
                                "EOS",
                                ""
                            ]
                        }
                    }
                */


                //for Japanese we want to segment it into "words"
                //   $passage = utils::segment_japanese($phrase);

                //First check if the phrase is in our cache
                //TO DO make a proper cache definition ...https://docs.moodle.org/dev/Cache_API#Getting_a_cache_object
                //fails on Japanese sometimes .. error unserialising on $cache->get .. which kills modal form submission
                $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'jpphrases');
                $phrasekey = sha1($phrase);
                try {
                    $phones_and_segments = $cache->get($phrasekey);
                }catch(\Exception $e){
                    //fails on japanese for some reason, but we cant dwell on it,
                    $phones_and_segments =false;
                }
                //if we have phones and segments cached, yay
                if($phones_and_segments){
                    return $phones_and_segments;
                }

                //send out for the phonetic processing for japanese text
                //turn numbers into hankaku first // this could be skipped possibly
                //transcripts are usually hankaku but phonetics shouldnt be different either way
                //except they seem to come back as numbers if zenkaku which is better than ni ni for 22
                $phrase = mb_convert_kana($phrase,"n");
                $postdata =array('passage'=>$phrase);
                $results = self::curl_fetch($katakanify_url,$postdata,'post');
                if(!self::is_json($results)){return false;}

                $jsonresults = json_decode($results);
                $nodes=[];
                $words=[];
                if($jsonresults && $jsonresults->status==true){
                    foreach($jsonresults->data->results as $result){
                        $bits = preg_split("/\t+/", $result);
                        if(count($bits)>1) {
                            $nodes[] = $bits[1];
                            $words[] = $bits[0];
                        }
                    }
                }

                //process nodes
                $katakanaarray=[];
                $segmentarray=[];
                $nodeindex=-1;
                foreach ($nodes as $n) {
                    $nodeindex++;
                    $analysis = explode(',',$n);
                    if(count($analysis) > 5) {
                        switch($analysis[0]) {
                            case '記号':
                                $segmentcount = count($segmentarray);
                                if($segmentcount>0){
                                    $segmentarray[$segmentcount-1].=$words[$nodeindex];
                                }
                                break;
                            default:
                                $reading = '*';
                                if(count($analysis) > 7) {
                                    $reading = $analysis[7];
                                }
                                if ($reading != '*') {
                                    $katakanaarray[] = $reading;
                                } else if($analysis[1]=='数'){
                                    //numbers dont get phoneticized
                                    $katakanaarray[] = $words[$nodeindex];
                                }
                                $segmentarray[]=$words[$nodeindex];
                        }
                    }
                }
                if($segmented) {
                    $phonetic = implode(' ',$katakanaarray);
                    $segments = implode(' ',$segmentarray);
                }else {
                    $phonetic = implode('',$katakanaarray);
                    $segments = implode('',$segmentarray);
                }
                //cache results, so the same data coming again returns faster and saves traffic
                $phones_and_segments = [$phonetic,$segments];
                $cache->set($phrasekey,$phones_and_segments );
                break;

            default:
                $phonetic = '';
                $segments = $phrase;
                $phones_and_segments = [$phonetic,$segments];
        }
        return $phones_and_segments;
    }

    //fetch lang server url, services incl. 'transcribe' , 'lm', 'lt', 'spellcheck', 'katakanify'
    public static function fetch_lang_server_url($region,$service='transcribe'){
        switch($region) {
            case 'useast1':
                $ret = 'https://useast.ls.poodll.com/';
                break;
            default:
                $ret = 'https://' . $region . '.ls.poodll.com/';
        }
        return $ret . $service;
    }

    public static function fetch_options_reportstable() {
        $options = array(constants::M_USE_DATATABLES => get_string("reporttableajax", constants::M_COMPONENT),
            constants::M_USE_PAGEDTABLES => get_string("reporttablepaged", constants::M_COMPONENT));
        return $options;
    }

    public static function fetch_options_transcribers() {
        $options = array(constants::TRANSCRIBER_AUTO => get_string("transcriber_auto", constants::M_COMPONENT),
            constants::TRANSCRIBER_POODLL => get_string("transcriber_poodll", constants::M_COMPONENT));
        return $options;
    }

    public static function fetch_options_animations(){
        return array(
            constants::M_ANIM_FANCY=> get_string('anim_fancy', constants::M_COMPONENT),
            constants::M_ANIM_PLAIN => get_string('anim_plain', constants::M_COMPONENT));
    }

    public static function fetch_options_textprompt() {
        $options = array(constants::TEXTPROMPT_DOTS => get_string("textprompt_dots", constants::M_COMPONENT),
                constants::TEXTPROMPT_WORDS => get_string("textprompt_words", constants::M_COMPONENT));
        return $options;
    }

    public static function fetch_options_yesno() {
        $yesnooptions = array(1 => get_string('yes'), 0 => get_string('no'));
        return $yesnooptions;
    }

    public static function fetch_options_listenorread() {
        $options = array(constants::LISTENORREAD_READ => get_string("listenorread_read", constants::M_COMPONENT),
                constants::LISTENORREAD_LISTEN => get_string("listenorread_listen", constants::M_COMPONENT),
                    constants::LISTENORREAD_LISTENANDREAD => get_string("listenorread_listenandread", constants::M_COMPONENT));
        return $options;
    }

    public static function fetch_pagelayout_options(){
        $options = Array(
                'standard'=>'standard',
                'embedded'=>'embedded',
                'popup'=>'popup'
        );
        return $options;
    }


    public static function pack_ttspassageopts($data)
    {
        $opts = new \stdClass();
        //This is probably over caution, but just in case the data comes in wrong, we want to fall back on something
        if (isset($opts->{constants::TTSPASSAGEVOICE})) {
            $opts->{constants::TTSPASSAGEVOICE} = $data->{constants::TTSPASSAGEVOICE};
            $opts->{constants::TTSPASSAGESPEED} = $data->{constants::TTSPASSAGESPEED};
        }else{
            $opts->{constants::TTSPASSAGEVOICE} = 'Salli';
            $opts->{constants::TTSPASSAGESPEED} = constants::TTS_NORMAL;
        }
        $opts_json = json_encode($opts);
        return $opts_json;
    }

    public static function unpack_ttspassageopts($data){
        if(!self::is_json($data->{constants::TTSPASSAGEOPTS})){return $data;}
        $opts = json_decode($data->{constants::TTSPASSAGEOPTS});

        //Overcaution follows ....
        if(isset($opts->{constants::TTSPASSAGESPEED})) {
            $data->{constants::TTSPASSAGESPEED} = $opts->{constants::TTSPASSAGESPEED};
        }else{
            $data->{constants::TTSPASSAGESPEED}=false;
        }
        if(isset($opts->{constants::TTSPASSAGEVOICE})) {
            $data->{constants::TTSPASSAGEVOICE} = $opts->{constants::TTSPASSAGEVOICE};
        }else{
            $data->{constants::TTSPASSAGEVOICE}="Salli";
        }



        return $data;
    }
    public static function pack_ttsdialogopts($data){
        $opts = new \stdClass();
        //more overcaution
        if(isset($opts->{constants::TTSDIALOGVISIBLE})) {
            $opts->{constants::TTSDIALOGVISIBLE} = $data->{constants::TTSDIALOGVISIBLE};
        }else{
            $opts->{constants::TTSDIALOGVISIBLE}=false;
        }
        //loop through A,B and C slots and put the data together
        $voice_slots= [constants::TTSDIALOGVOICEA,constants::TTSDIALOGVOICEB,constants::TTSDIALOGVOICEC];
        foreach($voice_slots as $slot){
            if(isset($data->{$slot})){
                $opts->{$slot}=$data->{$slot};
            }else{
                $opts->{$slot}="Salli";
            }
        }

        $opts_json = json_encode($opts);
        return $opts_json;
    }
    public static function unpack_ttsdialogopts($data){
        if(!self::is_json($data->{constants::TTSDIALOGOPTS})){return $data;}
        //more overcaution
        $opts = json_decode($data->{constants::TTSDIALOGOPTS});
        if(isset($opts->{constants::TTSDIALOGVISIBLE})) {
            $data->{constants::TTSDIALOGVISIBLE} = $opts->{constants::TTSDIALOGVISIBLE};
        }else{
            $data->{constants::TTSDIALOGVISIBLE}=false;
        }

        //loop through A,B and C slots and put the data together
        $voice_slots= [constants::TTSDIALOGVOICEA,constants::TTSDIALOGVOICEB,constants::TTSDIALOGVOICEC];
        foreach($voice_slots as $slot){
            if(isset($opts->{$slot})){
                $data->{$slot}=$opts->{$slot};
            }else{
                $data->{$slot}="Salli";
            }
        }

        return $data;
    }

    public static function split_into_words($thetext) {
        $thetext = preg_replace('/\s+/', ' ', trim($thetext));
        if($thetext == ''){
            return array();
        }
        return explode(' ', $thetext);
    }

    public static function split_into_sentences($thetext) {
        $thetext = preg_replace('/\s+/', ' ', trim($thetext));
        if($thetext == ''){
            return array();
        }
        preg_match_all('/([^\.!\?]+[\.!\?"\']+)|([^\.!\?"\']+$)/', $thetext, $matches);
        return $matches[0];
    }

    public static function fetch_auto_voice($langcode){
        $showall=false;
        $voices = self::get_tts_voices($langcode,$showall);
        $autoindex = array_rand($voices);
        return $voices[$autoindex];
    }

    //can speak neural?
    public static function can_speak_neural($voice,$region){
        //check if the region is supported
        switch($region){
            case "useast1":
            case "tokyo":
            case "sydney":
            case "dublin":
            case "ottawa":
            case "frankfurt":
            case "london":
            case "singapore":
            case "mumbai":
                //ok
                break;
            default:
                return false;
        }

        //check if the voice is supported
        if(in_array($voice,constants::M_NEURALVOICES)){
            return true;
        }else{
            return false;
        }
    }

    public static function get_tts_options($no_ssml=false){
        $ret = array(constants::TTS_NORMAL=>get_string('ttsnormal',constants::M_COMPONENT),
            constants::TTS_SLOW=>get_string('ttsslow',constants::M_COMPONENT),
            constants::TTS_VERYSLOW=>get_string('ttsveryslow',constants::M_COMPONENT));
        if(!$no_ssml){$ret += array(constants::TTS_SSML=>get_string('ttsssml',constants::M_COMPONENT));}
        return $ret;
    }

    public static function get_tts_voices($langcode,$showall){
        $alllang= array(
                constants::M_LANG_ARAE => ['Zeina'=>'Zeina','ar-XA-Wavenet-B'=>'Amir_g','ar-XA-Wavenet-A'=>'Salma_g'],
            //constants::M_LANG_ARSA => [],
                constants::M_LANG_DEDE => ['Hans'=>'Hans','Marlene'=>'Marlene', 'Vicki'=>'Vicki','Daniel'=>'Daniel'],
                constants::M_LANG_DEAT => ['Hannah'=>'Hannah'],
                constants::M_LANG_DADK => ['Naja'=>'Naja','Mads'=>'Mads'],
            //constants::M_LANG_DECH => [],
                constants::M_LANG_ENUS => ['Joey'=>'Joey','Justin'=>'Justin','Kevin'=>'Kevin','Matthew'=>'Matthew','Ivy'=>'Ivy',
                        'Joanna'=>'Joanna','Kendra'=>'Kendra','Kimberly'=>'Kimberly','Salli'=>'Salli'],
                constants::M_LANG_ENGB => ['Brian'=>'Brian','Amy'=>'Amy', 'Emma'=>'Emma'],
                constants::M_LANG_ENAU => ['Russell'=>'Russell','Nicole'=>'Nicole','Olivia'=>'Olivia'],
                constants::M_LANG_ENNZ => ['Aria'=>'Aria'],
                constants::M_LANG_ENZA => ['Ayanda'=>'Ayanda'],
                constants::M_LANG_ENIN => ['Aditi'=>'Aditi', 'Raveena'=>'Raveena', 'Kajal'=>'Kajal'],
            // constants::M_LANG_ENIE => [],
                constants::M_LANG_ENWL => ["Geraint"=>"Geraint"],
            // constants::M_LANG_ENAB => [],
                constants::M_LANG_ESUS => ['Miguel'=>'Miguel','Penelope'=>'Penelope'],
                constants::M_LANG_ESES => [ 'Enrique'=>'Enrique', 'Conchita'=>'Conchita', 'Lucia'=>'Lucia'],
            //constants::M_LANG_FAIR => [],
                constants::M_LANG_FILPH => ['fil-PH-Wavenet-A'=>'Darna_g','fil-PH-Wavenet-B'=>'Reyna_g','fil-PH-Wavenet-C'=>'Bayani_g','fil-PH-Wavenet-D'=>'Ernesto_g'],
                constants::M_LANG_FIFI => ['Suvi'=>'Suvi','fi-FI-Wavenet-A'=>'Kaarina_g'],
                constants::M_LANG_FRCA => ['Chantal'=>'Chantal', 'Gabrielle'=>'Gabrielle','Liam'=>'Liam'],
                constants::M_LANG_FRFR => ['Mathieu'=>'Mathieu','Celine'=>'Celine', 'Lea'=>'Lea'],
                constants::M_LANG_HIIN => ["Aditi"=>"Aditi"],
                constants::M_LANG_HEIL => ['he-IL-Wavenet-A'=>'Sarah_g','he-IL-Wavenet-B'=>'Noah_g'],
                constants::M_LANG_HUHU => ['hu-HU-Wavenet-A'=>'Eszter_g'],
                constants::M_LANG_IDID => ['id-ID-Wavenet-A'=>'Guntur_g','id-ID-Wavenet-B'=>'Bhoomik_g'],
                constants::M_LANG_ITIT => ['Carla'=>'Carla',  'Bianca'=>'Bianca', 'Giorgio'=>'Giorgio'],
                constants::M_LANG_JAJP => ['Takumi'=>'Takumi','Mizuki'=>'Mizuki'],
                constants::M_LANG_KOKR => ['Seoyeon'=>'Seoyeon'],
            //constants::M_LANG_MSMY => [],
            //constants::M_LANG_MINZ => [],
                constants::M_LANG_NONO => ['Liv'=>'Liv','nb-NO-Wavenet-B'=>'Lars_g'],
                constants::M_LANG_NLNL => ["Ruben"=>"Ruben","Lotte"=>"Lotte","Laura"=>"Laura"],
                constants::M_LANG_NLBE => ["nl-BE-Wavenet-B"=>"Marc_g","nl-BE-Wavenet-A"=>"Marie_g"],
                constants::M_LANG_PLPL => ['Ewa'=>'Ewa','Maja'=>'Maja','Jacek'=>'Jacek','Jan'=>'Jan'],
                constants::M_LANG_PTBR => ['Ricardo'=>'Ricardo', 'Vitoria'=>'Vitoria','Camila'=>'Camila'],
                constants::M_LANG_PTPT => ["Ines"=>"Ines",'Cristiano'=>'Cristiano'],
                constants::M_LANG_RORO => ['Carmen'=>'Carmen','ro-RO-Wavenet-A'=>'Sorina_g'],
                constants::M_LANG_RURU => ["Tatyana"=>"Tatyana","Maxim"=>"Maxim"],
                constants::M_LANG_SVSE => ['Astrid'=>'Astrid'],
                constants::M_LANG_TAIN => ['ta-IN-Wavenet-A'=>'Dyuthi_g','ta-IN-Wavenet-B'=>'Bhoomik_g'],
                constants::M_LANG_TEIN => ['te-IN-Standard-A'=>'Anandi_g','te-IN-Standard-B'=>'Kai_g'],
                constants::M_LANG_TRTR => ['Filiz'=>'Filiz'],
                constants::M_LANG_UKUA => ['uk-UA-Wavenet-A'=>'Katya_g'],
                constants::M_LANG_ZHCN => ['Zhiyu'=>'Zhiyu']

        );
        if(array_key_exists($langcode,$alllang) && !$showall) {
            return $alllang[$langcode];
        }elseif($showall) {
            $usearray =[];

            //add current language first
            foreach($alllang[$langcode] as $v=>$thevoice){
                $neuraltag = in_array($v,constants::M_NEURALVOICES) ? ' (+)' : '';
                $usearray[$v] = get_string(strtolower($langcode), constants::M_COMPONENT) . ': ' . $thevoice . $neuraltag;
            }
            //then all the rest
            foreach($alllang as $lang=>$voices){
                if($lang==$langcode){continue;}
                foreach($voices as $v=>$thevoice){
                    $neuraltag = in_array($v,constants::M_NEURALVOICES) ? ' (+)' : '';
                    $usearray[$v] = get_string(strtolower($lang), constants::M_COMPONENT) . ': ' . $thevoice . $neuraltag;
                }
            }
            return $usearray;
        }else{
                return $alllang[constants::M_LANG_ENUS];
        }
    }

    public static function get_lang_options(){
       return array(
               constants::M_LANG_ARAE => get_string('ar-ae', constants::M_COMPONENT),
               constants::M_LANG_ARSA => get_string('ar-sa', constants::M_COMPONENT),
               constants::M_LANG_DADK => get_string('da-dk', constants::M_COMPONENT),
               constants::M_LANG_DEDE => get_string('de-de', constants::M_COMPONENT),
               constants::M_LANG_DEAT => get_string('de-at', constants::M_COMPONENT),
               constants::M_LANG_DECH => get_string('de-ch', constants::M_COMPONENT),
               constants::M_LANG_ENUS => get_string('en-us', constants::M_COMPONENT),
               constants::M_LANG_ENGB => get_string('en-gb', constants::M_COMPONENT),
               constants::M_LANG_ENAU => get_string('en-au', constants::M_COMPONENT),
               constants::M_LANG_ENNZ => get_string('en-nz', constants::M_COMPONENT),
               constants::M_LANG_ENZA => get_string('en-za', constants::M_COMPONENT),
               constants::M_LANG_ENIN => get_string('en-in', constants::M_COMPONENT),
               constants::M_LANG_ENIE => get_string('en-ie', constants::M_COMPONENT),
               constants::M_LANG_ENWL => get_string('en-wl', constants::M_COMPONENT),
               constants::M_LANG_ENAB => get_string('en-ab', constants::M_COMPONENT),
               constants::M_LANG_ESUS => get_string('es-us', constants::M_COMPONENT),
               constants::M_LANG_ESES => get_string('es-es', constants::M_COMPONENT),
               constants::M_LANG_FAIR => get_string('fa-ir', constants::M_COMPONENT),
               constants::M_LANG_FILPH => get_string('fil-ph', constants::M_COMPONENT),
               constants::M_LANG_FRCA => get_string('fr-ca', constants::M_COMPONENT),
               constants::M_LANG_FRFR => get_string('fr-fr', constants::M_COMPONENT),
               constants::M_LANG_HIIN => get_string('hi-in', constants::M_COMPONENT),
               constants::M_LANG_HEIL => get_string('he-il', constants::M_COMPONENT),
               constants::M_LANG_IDID => get_string('id-id', constants::M_COMPONENT),
               constants::M_LANG_ITIT => get_string('it-it', constants::M_COMPONENT),
               constants::M_LANG_JAJP => get_string('ja-jp', constants::M_COMPONENT),
               constants::M_LANG_KOKR => get_string('ko-kr', constants::M_COMPONENT),
               constants::M_LANG_MSMY => get_string('ms-my', constants::M_COMPONENT),
               constants::M_LANG_NLNL => get_string('nl-nl', constants::M_COMPONENT),
               constants::M_LANG_NLBE => get_string('nl-be', constants::M_COMPONENT),
               constants::M_LANG_PTBR => get_string('pt-br', constants::M_COMPONENT),
               constants::M_LANG_PTPT => get_string('pt-pt', constants::M_COMPONENT),
               constants::M_LANG_RURU => get_string('ru-ru', constants::M_COMPONENT),
               constants::M_LANG_TAIN => get_string('ta-in', constants::M_COMPONENT),
               constants::M_LANG_TEIN => get_string('te-in', constants::M_COMPONENT),
               constants::M_LANG_TRTR => get_string('tr-tr', constants::M_COMPONENT),
               constants::M_LANG_ZHCN => get_string('zh-cn', constants::M_COMPONENT),
           constants::M_LANG_MINZ => get_string('mi-nz', constants::M_COMPONENT),
           constants::M_LANG_NONO => get_string('no-no', constants::M_COMPONENT),
           constants::M_LANG_PLPL => get_string('pl-pl', constants::M_COMPONENT),
           constants::M_LANG_RORO => get_string('ro-ro', constants::M_COMPONENT),
           constants::M_LANG_SVSE => get_string('sv-se', constants::M_COMPONENT),
           constants::M_LANG_UKUA => get_string('uk-ua',constants::M_COMPONENT),
           constants::M_LANG_EUES => get_string('eu-es',constants::M_COMPONENT),
           constants::M_LANG_FIFI => get_string('fi-fi',constants::M_COMPONENT),
           constants::M_LANG_HUHU => get_string('hu-hu',constants::M_COMPONENT)
       );
   }

    public static function get_prompttype_options() {
        return array(
                constants::M_PROMPT_SEPARATE => get_string('prompt-separate', constants::M_COMPONENT),
                constants::M_PROMPT_RICHTEXT => get_string('prompt-richtext', constants::M_COMPONENT)
        );

    }

    public static function get_containerwidth_options() {
        return array(
            constants::M_CONTWIDTH_COMPACT => get_string('contwidth-compact', constants::M_COMPONENT),
            constants::M_CONTWIDTH_WIDE => get_string('contwidth-wide', constants::M_COMPONENT),
            constants::M_CONTWIDTH_FULL => get_string('contwidth-full', constants::M_COMPONENT)
        );

    }


        public static function prepare_file_and_json_stuff($moduleinstance, $modulecontext){

            $ednofileoptions = minispeak_editor_no_files_options($modulecontext);
            $editors  = minispeak_get_editornames();

            $itemid = 0;
            foreach($editors as $editor){
                $moduleinstance = file_prepare_standard_editor((object)$moduleinstance,$editor, $ednofileoptions, $modulecontext,constants::M_COMPONENT,$editor, $itemid);
            }

            return $moduleinstance;

        }//end of prepare_file_and_json_stuff

    public static function clean_ssml_chars($speaktext){
        //deal with SSML reserved characters
        $speaktext =  str_replace("&", "&amp;", $speaktext);
        $speaktext = str_replace("'", "&apos;", $speaktext);
        $speaktext = str_replace('"', "&quot;", $speaktext);
        $speaktext = str_replace("<", "&lt;", $speaktext);
        $speaktext =  str_replace(">", "&gt;", $speaktext);
        return $speaktext;
    }

    //fetch the MP3 URL of the text we want read aloud
    public static function fetch_polly_url($token,$region,$speaktext,$voiceoption, $voice) {
        global $USER;

        $texttype='ssml';
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'polly');
        $key = sha1($speaktext . '|' . $texttype . '|' . $voice);
        $pollyurl = $cache->get($key);
        if($pollyurl && !empty($pollyurl)){
            return $pollyurl;
        }



        switch((int)($voiceoption)){

            //slow
            case 1:
                //fetch slightly slower version of speech
                //rate = 'slow' or 'x-slow' or 'medium'
                $speaktext =self::clean_ssml_chars($speaktext);
                $speaktext = '<speak><break time="1000ms"></break><prosody rate="slow">' . $speaktext . '</prosody></speak>';
                break;
            //veryslow
            case 2:
                //fetch slightly slower version of speech
                //rate = 'slow' or 'x-slow' or 'medium'
                $speaktext =self::clean_ssml_chars($speaktext);
                $speaktext = '<speak><break time="1000ms"></break><prosody rate="x-slow">' . $speaktext . '</prosody></speak>';
                break;
            //ssml
            case 3:
                $speaktext='<speak>' . $speaktext . '</speak>';
                break;

            //normal
            case 0:
            default:
                //fetch slightly slower version of speech
                //rate = 'slow' or 'x-slow' or 'medium'
                $speaktext =self::clean_ssml_chars($speaktext);
                $speaktext = '<speak><break time="1000ms"></break>' . $speaktext . '</speak>';
                break;

        }

        //The REST API we are calling
        $functionname = 'local_cpapi_fetch_polly_url';

        //log.debug(params);
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['moodlewsrestformat'] = 'json';
        $params['text'] = urlencode($speaktext);
        $params['texttype'] = $texttype;
        $params['voice'] = $voice;
        $params['appid'] = constants::M_COMPONENT;;
        $params['owner'] = hash('md5',$USER->username);
        $params['region'] = $region;
        $params['engine'] = self::can_speak_neural($voice, $region)?'neural' : 'standard';
        $serverurl = self::CLOUDPOODLL . '/webservice/rest/server.php';
        $response = self::curl_fetch($serverurl, $params);
        if (!self::is_json($response)) {
            return false;
        }
        $payloadobject = json_decode($response);

        //returnCode > 0  indicates an error
        if (!isset($payloadobject->returnCode) || $payloadobject->returnCode > 0) {
            return false;
            //if all good, then lets do the embed
        } else if ($payloadobject->returnCode === 0) {
            $pollyurl = $payloadobject->returnMessage;
            //if its an S3 URL  then we cache it, yay
            if(\core_text::strpos($pollyurl,'pollyfile.poodll.net')>0) {
                $cache->set($key, $pollyurl);
            }
            return $pollyurl;
        } else {
            return false;
        }
    }

   public static function fetch_itemtypes_list(){
        $ret =[];
        $ret[constants::TYPE_MULTICHOICE] = get_string('multichoice', constants::M_COMPONENT);
        $ret[constants::TYPE_MULTIAUDIO] = get_string('multiaudio', constants::M_COMPONENT);
        $ret[constants::TYPE_DICTATIONCHAT] = get_string('dictationchat', constants::M_COMPONENT);
        $ret[constants::TYPE_DICTATION] = get_string('dictation', constants::M_COMPONENT);
        $ret[constants::TYPE_SPEECHCARDS] = get_string('speechcards', constants::M_COMPONENT);
        $ret[constants::TYPE_LISTENREPEAT] = get_string('listenrepeat', constants::M_COMPONENT);
        $ret[constants::TYPE_PAGE] = get_string('page', constants::M_COMPONENT);
        //$ret[constants::TYPE_SMARTFRAME] = get_string('smartframe', constants::M_COMPONENT);
        $ret[constants::TYPE_SHORTANSWER] = get_string('shortanswer', constants::M_COMPONENT);
        //$ret[constants::TYPE_COMPQUIZ] = get_string('compquiz', constants::M_COMPONENT);
       // $ret[constants::TYPE_BUTTONQUIZ] = get_string('buttonquiz', constants::M_COMPONENT);
        $ret[constants::TYPE_SGAPFILL] = get_string('sgapfill', constants::M_COMPONENT);
        $ret[constants::TYPE_LGAPFILL] = get_string('lgapfill', constants::M_COMPONENT);
        $ret[constants::TYPE_TGAPFILL] = get_string('tgapfill', constants::M_COMPONENT);
        return $ret;

   }

   public static function fetch_item_from_question($question, $context){
       //Set up the item type specific parts of the form data
       switch($question->type){
           case constants::TYPE_MULTICHOICE: return new local\itemtype\item_multichoice($question,$context);
           case constants::TYPE_MULTIAUDIO: return new local\itemtype\item_multiaudio($question,$context);
           case constants::TYPE_DICTATIONCHAT: return new local\itemtype\item_dictationchat($question,$context);
           case constants::TYPE_DICTATION: return new local\itemtype\item_dictation($question,$context);
           case constants::TYPE_SPEECHCARDS: return new local\itemtype\item_speechcards($question,$context);
           case constants::TYPE_LISTENREPEAT: return new local\itemtype\item_listenrepeat($question,$context);
           case constants::TYPE_PAGE: return new local\itemtype\item_page($question,$context);
           case constants::TYPE_SMARTFRAME: return new local\itemtype\item_smartframe($question,$context);
           case constants::TYPE_SHORTANSWER: return new local\itemtype\item_shortanswer($question,$context);
           case constants::TYPE_COMPQUIZ: return new local\itemtype\item_compquiz($question,$context);
           case constants::TYPE_BUTTONQUIZ: return new local\itemtype\item_buttonquiz($question,$context);
           case constants::TYPE_SGAPFILL: return new local\itemtype\item_speakinggapfill($question,$context);
           case constants::TYPE_LGAPFILL: return new local\itemtype\item_listeninggapfill($question,$context);
           case constants::TYPE_TGAPFILL: return new local\itemtype\item_typinggapfill($question,$context);
           default:
       }
   }

    public static function fetch_itemform_classname($itemtype){
        //Fetch the correct form
        switch($itemtype){
            case constants::TYPE_MULTICHOICE: return '\\'. constants::M_COMPONENT . '\local\itemform\multichoiceform';
            case constants::TYPE_MULTIAUDIO: return '\\'. constants::M_COMPONENT . '\local\itemform\multiaudioform';
            case constants::TYPE_DICTATIONCHAT: return '\\'. constants::M_COMPONENT . '\local\itemform\dictationchatform';
            case constants::TYPE_DICTATION: return '\\'. constants::M_COMPONENT . '\local\itemform\dictationform';
            case constants::TYPE_SPEECHCARDS: return '\\'. constants::M_COMPONENT . '\local\itemform\speechcardsform';
            case constants::TYPE_LISTENREPEAT: return '\\'. constants::M_COMPONENT . '\local\itemform\listenrepeatform';
            case constants::TYPE_PAGE: return '\\'. constants::M_COMPONENT . '\local\itemform\pageform';
            case constants::TYPE_SMARTFRAME: return '\\'. constants::M_COMPONENT . '\local\itemform\smartframe';
            case constants::TYPE_SHORTANSWER: return '\\'. constants::M_COMPONENT . '\local\itemform\shortanswerform';
            case constants::TYPE_COMPQUIZ: return '\\'. constants::M_COMPONENT . '\local\itemform\compquizform';
            case constants::TYPE_BUTTONQUIZ: return '\\'. constants::M_COMPONENT . '\ocal\itemform\buttonquizform';
            case constants::TYPE_SGAPFILL: return '\\'. constants::M_COMPONENT . '\local\itemform\speakinggapfillform';
            case constants::TYPE_LGAPFILL: return '\\'. constants::M_COMPONENT . '\local\itemform\listeninggapfillform';
            case constants::TYPE_TGAPFILL: return '\\'. constants::M_COMPONENT . '\local\itemform\typinggapfillform';
            default:
        }
    }

    public static function fetch_data_for_js($question,$context, $questionattempt){
        global $CFG,  $OUTPUT;


        //first confirm we are authorised before we try to get the token
        $config = get_config(constants::M_COMPONENT);
        if(empty($config->apiuser) || empty($config->apisecret)){
            $errormessage = get_string('nocredentials',constants::M_COMPONENT,
                $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            //return error?
            $token=false;
        }else {
            //fetch token
            $token = utils::fetch_token($config->apiuser,$config->apisecret);

            //check token authenticated and no errors in it
            $errormessage = utils::fetch_token_error($token);
            if(!empty($errormessage)){
                //return error?
                //return $this->show_problembox($errormessage);
            }
        }


        //prepare data
        $theitem=utils::fetch_item_from_question($question,$context);
        $theitem->set_token($token);
        $theitem->set_questionattempt($questionattempt);

        //add our item to test
        $renderer=$OUTPUT;
        $itemdata=$theitem->export_for_template($renderer);
        $itemdata->questiontype = $theitem;
        return $itemdata;
    }


  public static function fetch_item_amd($itemdata,$question){
        global $CFG, $USER, $PAGE;
        //any html we want to return to be sent to the page
        $ret_html = '';

        //here we set up any info we need to pass into javascript

        $recopts =Array();
        //recorder html ids
        $recopts['recorderid'] = constants::M_RECORDERID;
        $recopts['recordingcontainer'] = constants::M_RECORDING_CONTAINER;
        $recopts['recordercontainer'] = constants::M_RECORDER_CONTAINER;

        //activity html ids
        $recopts['passagecontainer'] = constants::M_PASSAGE_CONTAINER;
        $recopts['instructionscontainer'] = constants::M_INSTRUCTIONS_CONTAINER;
        $recopts['recordbuttoncontainer'] =constants::M_RECORD_BUTTON_CONTAINER;
        $recopts['startbuttoncontainer'] =constants::M_START_BUTTON_CONTAINER;
        $recopts['hider']=constants::M_HIDER;
        $recopts['progresscontainer'] = constants::M_PROGRESS_CONTAINER;
        $recopts['feedbackcontainer'] = constants::M_FEEDBACK_CONTAINER;
        $recopts['wheretonextcontainer'] = constants::M_WHERETONEXT_CONTAINER;
        $recopts['quizcontainer'] = constants::M_QUIZ_CONTAINER;
        $recopts['errorcontainer'] = constants::M_ERROR_CONTAINER;

        //first confirm we are authorised before we try to get the token
        $config = get_config(constants::M_COMPONENT);
        if(empty($config->apiuser) || empty($config->apisecret)){
            $errormessage = get_string('nocredentials',constants::M_COMPONENT,
                $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            return self::show_problembox($errormessage);
        }else {
            //fetch token
            $token = utils::fetch_token($config->apiuser,$config->apisecret);

            //check token authenticated and no errors in it
            $errormessage = utils::fetch_token_error($token);
            if(!empty($errormessage)){
                return self::show_problembox($errormessage);
            }
        }
        $recopts['token']=$token;
        $recopts['owner']=hash('md5',$USER->username);
        $recopts['region']=$config->awsregion;
        $recopts['ttslanguage']=$question->ttslanguage;
        //TO DO  - set the item transcriber in the itemdata
        $recopts['stt_guided']=true;//$itemdata->transcriber==constants::TRANSCRIBER_POODLL;


        $recopts['useanimatecss']=$config->animations==constants::M_ANIM_FANCY;
        $recopts['quizdata'] = [$itemdata];

        //this inits the M.mod_minilesson thingy, after the page has loaded.
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        //convert opts to json
        $jsonstring = json_encode($recopts);
        $widgetid = constants::M_RECORDERID . '_opts_9999';
        $opts_html = \html_writer::tag('input', '', array('id' => 'amdopts_' . $widgetid, 'type' => 'hidden', 'value' => $jsonstring));

        //the recorder div
        $ret_html = $ret_html . $opts_html;
        //TO DO we fudge a cmid and its not really needed
        $opts=array('cmid'=>$question->id,'widgetid'=>$widgetid);
        if (empty($itemdata->locked)) {
            $PAGE->requires->js_call_amd("qtype_minispeak/activitycontroller", 'init', array($opts));
        }


        //these need to be returned and echo'ed to the page
        return $ret_html;
    }

    /**
     * Return HTML to display message about problem
     */
    public static function show_problembox($msg) {
        global $OUTPUT;

        $output = '';
        $output .= $OUTPUT->box_start(constants::M_COMPONENT . '_problembox');
        $output .= $OUTPUT->notification($msg, 'warning');
        $output .= $OUTPUT->box_end();
        return $output;
    }

    public static function fetch_editor_options( $modulecontext)
    {
        $maxfiles = 99;
        $maxbytes = 0;
        return array('trusttext' => 0,'noclean'=>1, 'subdirs' => true, 'maxfiles' => $maxfiles,
            'maxbytes' => $maxbytes, 'context' => $modulecontext);
    }

    public static function fetch_filemanager_options($maxfiles = 1)
    {
        $maxbytes = 0;
        return array('subdirs' => true, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'accepted_types' => array('audio', 'video','image'));
    }

    public static function decode_payload($payload, $secret) {
        if (empty($payload) || empty($secret)) {
            return null;
        }
        try {
            $payload = JWT::decode($payload, $secret, ['HS256']);
            $payload = base64_decode($payload);
            return json_decode($payload, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function do_mb_str_split($string, $split_length = 1, $encoding = null)
    {
        //for greater than PHP 7.4
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            // Code for PHP 7.4 and above
            return mb_str_split($string, $split_length, $encoding);
        }

        //for less than PHP 7.4
        if (null !== $string && !\is_scalar($string) && !(\is_object($string) && \method_exists($string, '__toString'))) {
            trigger_error('mb_str_split(): expects parameter 1 to be string, '.\gettype($string).' given', E_USER_WARNING);
            return null;
        }
        if (null !== $split_length && !\is_bool($split_length) && !\is_numeric($split_length)) {
            trigger_error('mb_str_split(): expects parameter 2 to be int, '.\gettype($split_length).' given', E_USER_WARNING);
            return null;
        }
        $split_length = (int) $split_length;
        if (1 > $split_length) {
            trigger_error('mb_str_split(): The length of each segment must be greater than zero', E_USER_WARNING);
            return false;
        }
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        } else {
            $encoding = (string) $encoding;
        }

        if (! in_array($encoding, mb_list_encodings(), true)) {
            static $aliases;
            if ($aliases === null) {
                $aliases = [];
                foreach (mb_list_encodings() as $encoding) {
                    $encoding_aliases = mb_encoding_aliases($encoding);
                    if ($encoding_aliases) {
                        foreach ($encoding_aliases as $alias) {
                            $aliases[] = $alias;
                        }
                    }
                }
            }
            if (! in_array($encoding, $aliases, true)) {
                trigger_error('mb_str_split(): Unknown encoding "'.$encoding.'"', E_USER_WARNING);
                return null;
            }
        }

        $result = [];
        $length = mb_strlen($string, $encoding);
        for ($i = 0; $i < $length; $i += $split_length) {
            $result[] = mb_substr($string, $i, $split_length, $encoding);
        }
        return $result;
    }

}
