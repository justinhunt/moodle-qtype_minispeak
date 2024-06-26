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

namespace qtype_minispeak\local\itemtype;

use html_writer;
use qtype_minispeak\constants;
use qtype_minispeak\utils;
use templatable;
use renderable;
use stdClass;

/**
 * Renderable class for an item in a minispeak question type.
 *
 * @package    qtype_minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class item implements templatable, renderable {

    /** @var boolean force titles */
    protected $forcetitles;

    /** @var array editor optiosns */
    protected $editoroptions;

    /** @var array filemanager options */
    protected $filemanageroptions;

    /** @var int $id The current number in the lesson/container */
    protected $currentnumber;

    /** @var \stdClass $questionattempt */
    protected $questionattempt;

    /** @var \stdClass $itemrecord The db record for the item. */
    protected $itemrecord;

    /** @var \stdClass $context The mod context. */
    protected $context;

    /** @var \stdClass $course The mod context. */
    protected $course;


    /** @var string $token The cloudpoodll token. */
    protected $token;

    /** @var string $region The aws region. */
    protected $region;

    /** @var string $language The target language. */
    protected $language;

    /** @var \stdClass $moduleinstance The module. */
    protected $moduleinstance;

    //NEEDS SPEECH REC
    protected $needs_speechrec  =false;

    /** @var bool $hassubitems indicates whether subitems or single item */
    protected $hassubitems = true;

    /**
     * The class constructor.
     *
     */
    public function __construct($question, $context){
        $this->from_question($question,$context);
    }

    /**
     * The class constructor.
     *
     * @param int $currentnumber The current number in the lesson
     * @param \stdClass $itemrecord The db record for the item.
     */
    /*
    public static function from_id($itemid,$moduleinstance=false, $context=false) {
        global $DB;

        $itemrecord = $DB->get_record(constants::M_QTABLE,['id'=>$itemid],'*', MUST_EXIST);
        $instance=self::from_record($itemrecord,$moduleinstance,$context);
        return $instance;
    }
    */

    /**


     * The class constructor.
     *
     * @param int $currentnumber The current number in the lesson
     * @param \stdClass $itemrecord The db record for the item.
     */
    public function from_question($question, $context) {
        global $DB;
        $config=get_config(constants::M_COMPONENT);
        $itemrecord=false;
        if(isset($question->id) && !empty($question->id)) {
            $itemrecord = $DB->get_record(constants::M_QTABLE, ['questionid' => $question->id]);
        }
        if(!$itemrecord){
            $itemrecord = new \stdClass();
        }

        //extract the options from the question, and save them in the itemrecord
        foreach (constants::M_EXTRA_FIELDS as $field) {
            if (isset($question->{$field})) {
                $itemrecord->{$field} = $question->{$field};
            }
        }
        //extract any file area stuff
        foreach (constants::M_FILE_AREAS as $filearea){
            if (isset($question->{$filearea})) {
                $itemrecord->{$filearea} = $question->{$filearea};
            }
        }

        $this->itemrecord = $itemrecord;
        $this->question=$question;



        $this->context = $context;
        $this->region= $config->awsregion;
        $this->language= isset($this->itemrecord->ttslanguage) ? $this->itemrecord->ttslanguage : $config->ttslanguage;

        //we call the question module instance, for consistency with minilesson, so we can copy and paste without lots of fiddling
        $this->moduleinstance=$question;


        if(!empty($token)) {
            $this->token = $token;
        }
        $this->editoroptions = utils::fetch_editor_options($this->context);
        $this->filemanageroptions = utils::fetch_filemanager_options();

        // TO DO set these props
        /*
        $this->forcetitles = $this->moduleinstance->showqtitles;
        $this->region = $this->moduleinstance->region;
        $this->language = $this->moduleinstance->ttslanguage;
        */

    }

    public function set_questionattempt($questionattempt){
        $this->questionattempt = $questionattempt;
        $this->set_currentnumber($questionattempt->get_slot());
    }

    public function set_token($token){
        $this->token = $token;
    }

    public function set_currentnumber($currentnumber){
        $this->currentnumber = $currentnumber;
    }


    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the item
     * @return array
     */
    public function export_for_template(\renderer_base $output){
        $testitem= new \stdClass();
        $testitem = $this->get_common_elements($testitem);
        $testitem = $this->get_text_answer_elements($testitem);
        $testitem = $this->get_polly_options($testitem);
        $testitem = $this->set_layout($testitem);

        return $testitem;
    }

    protected function get_common_elements($testitem){
        global $CFG;

        $itemrecord = $this->itemrecord;
        $editoroptions = $this->editoroptions;

        //the basic item attributes
        $testitem->number =  $this->currentnumber;
        $testitem->correctanswer =  $this->itemrecord->correctanswer;
        $testitem->id = $this->itemrecord->id;
        $testitem->type=$this->itemrecord->type;
        $testitem->timelimit=$this->itemrecord->timelimit;
        if($this->forcetitles){$testitem->title=$this->itemrecord->name;}
        $testitem->uniqueid=$this->itemrecord->type . $testitem->number;
        $testitem->typenicename = utils::fetch_itemtypes_list()[$testitem->type] ?? null;
        $testitem->allowretry = true;
        $testitem->hassubitems = !empty($this->hassubitems);

        //Question instructions
        if(!empty($itemrecord->{constants::TEXTINSTRUCTIONS})) {
            $testitem->iteminstructions = $itemrecord->{constants::TEXTINSTRUCTIONS};
        }

        //Question Text
        $itemtext =  file_rewrite_pluginfile_urls($itemrecord->{constants::TEXTQUESTION},
            'pluginfile.php', $this->context->id,constants::M_COMPONENT,
            constants::TEXTQUESTION_FILEAREA, $testitem->id);
        $itemtext = format_text($itemtext, FORMAT_MOODLE, $editoroptions);
        if(!empty($itemtext)) {
            $testitem->itemtext = $itemtext;
        }

        //Question media embed
        if(!empty($itemrecord->{constants::MEDIAIFRAME}) && !empty(trim($itemrecord->{constants::MEDIAIFRAME}))){
            $testitem->itemiframe=$itemrecord->{constants::MEDIAIFRAME};
        }

        //Question media items (upload)
        $mediaurls =$this->fetch_media_urls(constants::MEDIAQUESTION,$itemrecord);
        if($mediaurls && count($mediaurls)>0){
            foreach($mediaurls as $mediaurl){
                $file_parts = pathinfo(strtolower($mediaurl));
                switch($file_parts['extension'])
                {
                    case "jpg":
                    case "jpeg":
                    case "png":
                    case "gif":
                    case "bmp":
                    case "svg":
                        $testitem->itemimage = $mediaurl;
                        break;

                    case "mp4":
                    case "mov":
                    case "webm":
                    case "ogv":
                        $testitem->itemvideo = $mediaurl;
                        break;

                    case "m4a":
                    case "mp3":
                    case "ogg":
                    case "wav":
                        $testitem->itemaudio = $mediaurl;
                        break;

                    default:
                        //do nothing
                }//end of extension switch
            }//end of for each
        }//end of if mediaurls

        //TTS Question
        if(!empty($itemrecord->{constants::TTSQUESTION}) && !empty(trim($itemrecord->{constants::TTSQUESTION}))){
            $testitem->itemttsaudio=$itemrecord->{constants::TTSQUESTION};
            $testitem->itemttsaudiovoice=$itemrecord->{constants::TTSQUESTIONVOICE};
            $testitem->itemttsoption=$itemrecord->{constants::TTSQUESTIONOPTION};
            $testitem->itemttsautoplay=$itemrecord->{constants::TTSAUTOPLAY};
        }
        //YT Clip
        if(!empty($itemrecord->{constants::YTVIDEOID}) && !empty(trim($itemrecord->{constants::YTVIDEOID}))){
            $ytvideoid = trim($itemrecord->{constants::YTVIDEOID});
            //if its a YT URL we want to parse the id from it
            if(\core_text::strlen($ytvideoid)>11){
                $urlbits=[];
                preg_match('/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/', $ytvideoid, $urlbits);
                if($urlbits && count($urlbits)>7){
                    $ytvideoid=$urlbits[7];
                }
            }
            //
            $testitem->itemytvideoid=$ytvideoid;
            $testitem->itemytvideostart=$itemrecord->{constants::YTVIDEOSTART};
            $testitem->itemytvideoend=$itemrecord->{constants::YTVIDEOEND};
        }
        //TTS Dialog
        if(!empty($itemrecord->{constants::TTSDIALOG}) && !empty(trim($itemrecord->{constants::TTSDIALOG}))){
            $itemrecord = utils::unpack_ttsdialogopts($itemrecord);
            $testitem->itemttsdialog=true;
            $testitem->itemttsdialogvisible=$itemrecord->{constants::TTSDIALOGVISIBLE};
            $dialoglines = explode(PHP_EOL,$itemrecord->{constants::TTSDIALOG});
            $linesdata=[];
            foreach($dialoglines as $theline){
                if(\core_text::strlen($theline)>1) {
                    $startchars = \core_text::substr($theline, 0, 2);
                    switch($startchars){
                        case 'A)':
                            $speaker="a";
                            $voice=$itemrecord->{constants::TTSDIALOGVOICEA};
                            $thetext = \core_text::substr($theline, 2);
                            break;
                        case 'B)':
                            $speaker="b";
                            $voice=$itemrecord->{constants::TTSDIALOGVOICEB};
                            $thetext = \core_text::substr($theline, 2);
                            break;
                        case 'C)':
                            $speaker="c";
                            $voice=$itemrecord->{constants::TTSDIALOGVOICEC};
                            $thetext = \core_text::substr($theline, 2);
                            break;
                        case '>>':
                            $speaker="soundeffect";
                            $voice="soundeffect";
                            $thetext = \core_text::substr($theline, 2);
                            break;
                        default:
                            //if it's just a new line for the previous voice
                            if(count($linesdata)>0){
                                $voice=$linesdata[count($linesdata)-1]->voice;
                                $speaker=$linesdata[count($linesdata)-1]->actor;
                                //if they never entered A) B) or C)
                            }else{
                                $voice=$itemrecord->{constants::TTSDIALOGVOICEA};
                                $speaker="a";
                            }
                            $thetext = $theline;

                    }
                    if(empty(trim($thetext))){continue;}
                    $lineset=new \stdClass();
                    $lineset->speaker=$speaker;
                    $lineset->speakertext=$thetext;
                    $lineset->voice=$voice;
                    $voiceoptions = constants::TTS_NORMAL;
                    if($lineset->voice=="soundeffect"){
                        $lineset->audiourl = $CFG->wwwroot  . '/' . constants::M_PATH . '/sounds/' . trim($thetext) . '.mp3';
                    }else {
                        $lineset->audiourl = utils::fetch_polly_url($this->token, 'useast1', $thetext, $voiceoptions, $voice);
                    }
                    $linesdata[] = $lineset;

                }

            }
            $testitem->ttsdialoglines = $linesdata;

        }// end of tts dialog

        //TTS Passage
        if(!empty($itemrecord->{constants::TTSPASSAGE}) && !empty(trim($itemrecord->{constants::TTSPASSAGE}))){
            $itemrecord = utils::unpack_ttspassageopts($itemrecord);
            $testitem->itemttspassage=true;
            $textlines = utils::split_into_sentences($itemrecord->{constants::TTSPASSAGE});
            $voice = $itemrecord->{constants::TTSPASSAGEVOICE};
            $voiceoptions = $itemrecord->{constants::TTSPASSAGESPEED};
            $linedatas=[];
            foreach($textlines as $theline){
                if(!empty(trim($theline))) {
                    $linedata=new \stdClass();
                    $linedata->sentence = $theline;
                    $linedata->audiourl = utils::fetch_polly_url($this->token, 'useast1', $theline, $voiceoptions, $voice);
                    $linedatas[]=$linedata;
                }
            }
            $testitem->ttspassagelines = $linedatas;
        }// end of tts dialog

        //Question TextArea
        if(!empty($itemrecord->{constants::QUESTIONTEXTAREA}) && !empty(trim($itemrecord->{constants::QUESTIONTEXTAREA}))){
            $testitem->itemtextarea=nl2br($itemrecord->{constants::QUESTIONTEXTAREA});
        }

        //show text prompt or dots, for listen and repeat really
        $testitem->show_text=$itemrecord->{constants::SHOWTEXTPROMPT};

        //For right to left languages we want to add the RTL direction and right justify.
        switch($this->moduleinstance->ttslanguage){
            case constants::M_LANG_ARAE:
            case constants::M_LANG_ARSA:
            case constants::M_LANG_FAIR:
            case constants::M_LANG_HEIL:
                $testitem->rtl=constants::M_CLASS . '_rtl';
                break;
            default:
                $testitem->rtl='';
        }

        return $testitem;
    }

    protected function get_text_answer_elements($testitem){
        $itemrecord = $this->itemrecord;
        //Text answer fields
        for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
            if(!empty($itemrecord->{constants::TEXTANSWER . $anumber}) && !empty(trim($itemrecord->{constants::TEXTANSWER . $anumber}))) {
                $testitem->{'customtext' . $anumber} = $itemrecord->{constants::TEXTANSWER . $anumber};
            }
        }
        return $testitem;
    }

    protected function get_polly_options($testitem){

        //if we need polly then lets do that
        $testitem->usevoice=$this->itemrecord->{constants::POLLYVOICE};
        $testitem->voiceoption=$this->itemrecord->{constants::POLLYOPTION};
        return $testitem;
    }

    protected function set_layout($testitem){

        //vertical layout or horizontal layout determined by content options
        $textset = isset($testitem->itemtextarea) && !empty($testitem->itemtextarea);
        $imageset = isset($testitem->itemimage) && !empty($testitem->itemimage);
        $videoset =isset($testitem->itemvideo) && !empty($testitem->itemvideo);
        $iframeset =isset($testitem->itemiframe) && !empty($testitem->itemiframe);
        $ytclipset =isset($testitem->itemytvideoid) && !empty($testitem->itemytvideoid);

        //layout
        $testitem->layout=$this->itemrecord->{constants::LAYOUT};
        if($testitem->layout==constants::LAYOUT_AUTO) {
            //if its not a page or shortanswer, any big content item will make it horizontal layout
            if ($testitem->type !== constants::TYPE_PAGE && $testitem->type !== constants::TYPE_SHORTANSWER) {
                if ($textset || $imageset || $videoset || $iframeset || $ytclipset) {
                    $testitem->horizontal = true;
                }
            }
        }else{
            switch($testitem->layout){
                case constants::LAYOUT_HORIZONTAL:
                    $testitem->horizontal = true;
                    break;
                case constants::LAYOUT_VERTICAL:
                    $testitem->vertical = true;
                    break;
                case constants::LAYOUT_MAGAZINE:
                    $testitem->magazine = true;
                    break;
            }
        }
        return $testitem;
    }

    /*
     * Processes gap fill sentences : TO DO not implemented, implement this
     */
    protected function process_gapfill_sentences($sentences){
        $sentenceobjects = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) {
                continue;
            }

            //TO DO replace [x] with gaps
            $prompt = $sentence;

            $s = new \stdClass();
            $s->index = $index;
            $s->indexplusone = $index + 1;
            $s->sentence = $sentence;
            $s->prompt = $prompt;
            $s->displayprompt = $prompt;
            $s->length = \core_text::strlen($s->sentence);

            $index++;
            $sentenceobjects[] = $s;
        }
        return $sentenceobjects;
    }

    /*
     * Processes gap fill sentences : TO DO not implemented, implement this
     */
    protected function process_typinggapfill_sentences($sentences) {
        return $this->parse_gapfill_sentences($sentences);
    }

    /*
     * Processes listening gap fill sentences : TO DO not implemented, implement this
     */
    protected function process_listeninggapfill_sentences($sentences) {
        return $this->parse_gapfill_sentences($sentences);
    }

    /*
     * Processes speaking gap fill sentences : TO DO not implemented, implement this
     */
    protected function process_speakinggapfill_sentences($sentences) {
        return $this->parse_gapfill_sentences($sentences);
    }

    /*
     * Processes listening gap fill sentences : TO DO not implemented, implement this
     */
    protected function parse_gapfill_sentences($sentences) {
        $index = 0;
        $sentenceobjects = [];

        foreach ($sentences as $sentence) {
            $arr = explode('|', trim($sentence));
            $sentence = trim($arr[0] ?? '');
            $definition = trim($arr[1] ?? '');
            if (empty($sentence)) {
                continue;
            }

            $parsedstring = [];
            $started = false;
            $words = explode(' ', $sentence);
            $maskedwords = [];
            foreach ($words as $index => $word) {
                if (strpos($word, '[') !== false) {
                    $maskedwords[$index] = str_replace(['[', ']', ',', '.'], ['', '', '', ''], $word);
                }
            }

            $enc = mb_detect_encoding($sentence);
            $characters = \mod_minilesson\utils::do_mb_str_split($sentence,1,$enc);

            $wordindex = 0;
            foreach ($characters as $character) {
                if ($character === ' ') {
                    $wordindex++;
                }
                if ($character === '[') {
                    $started = true;
                    continue;
                }
                if ($character === ']') {
                    $started = false;
                    continue;
                }
                if (array_key_exists($wordindex, $maskedwords) && $character !== " " && $character !== "." ) {
                    if ($started) {
                        $parsedstring[] = ['index' => $wordindex, 'character' => $character, 'type' => 'input'];
                    } else {
                        $parsedstring[] = ['index' => $wordindex, 'character' => $character, 'type' => 'mtext'];
                    }
                } else {
                    $parsedstring[] = ['index' => $wordindex, 'character' => $character, 'type' => 'text'];
                }
            }

            $sentence = str_replace(['[', ']', ',', '.'], ['', '', '', ''], $sentence);

            // TO DO replace [x] with gaps
            $prompt = $sentence;

            $s = new \stdClass();
            $s->index = $index;
            $s->indexplusone = $index + 1;
            $s->sentence = $sentence;
            $s->prompt = $prompt;
            $s->definition = $definition;
            $s->displayprompt = $prompt;
            $s->length = \core_text::strlen($s->sentence);
            $s->parsedstring = $parsedstring;
            $s->words = $maskedwords;
            $sentenceobjects[] = $s;
            $index++;
        }

        return $sentenceobjects;
    }

    /*
     * Takes an array of sentences and phonetics for the same, and returns sentence objects with display and spoken and phonetic data
     *
     */
    protected function process_spoken_sentences($sentences,$phonetics, $dottify=false, $is_ssml=false){
        //build a sentences object for mustache and JS
        $index = 0;
        $sentenceobjects = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) {
                continue;
            }

            //build prompt and displayprompt and sentence which could be different
            // prompt = audio_prompt
            // sentence = target_sentence ie what the student should say
            // displayprompt = the text_prompt that we show the student before they speak

            //dottify = if we dont show the text and just show dots ..
            if($dottify){
                $prompt = $this->dottify_text($sentence);
                $displayprompt = $prompt;

            }else{
                //if we have a pipe prompt = array[0] and response = array[1]
                $sentencebits = explode('|', $sentence);
                if (count($sentencebits) > 1) {
                    $prompt = trim($sentencebits[0]);
                    $sentence = trim($sentencebits[1]);
                    if(count($sentencebits) >2){
                        $displayprompt = trim($sentencebits[2]);
                    }else{
                        $displayprompt = $prompt;
                    }

                } else {
                    $prompt = $sentence;
                    $displayprompt = $sentence;
                }
            }

            //we strip the HTML tags off if it is SSML
            //probably no harm in doing this if its SSML or not ...
            if($is_ssml) {
                $displayprompt = strip_tags($displayprompt );
                $sentence = strip_tags($sentence);
            }

            if ($this->language == constants::M_LANG_JAJP) {
                $sentence = $this->process_japanese_phonetics($sentence);
            }

            $s = new \stdClass();
            $s->index = $index;
            $s->indexplusone = $index + 1;
            $s->sentence = $sentence;
            $s->prompt = $prompt;
            $s->displayprompt = $displayprompt;
            $s->length = \core_text::strlen($s->sentence);

            //add phonetics if we have them
            if(isset($phonetics[$index]) && !empty($phonetics[$index])){
                $s->phonetic=$phonetics[$index];
            }else{
                $s->phonetic='';
            }

            $index++;
            $sentenceobjects[] = $s;
        }
        return $sentenceobjects;
    }

    //by default we do nothing, but for japanese listen_and_speak, dictation chat and shortanswer, this is overrridden
    protected function process_japanese_phonetics($sentence){
        return $sentence;
    }

    protected function set_cloudpoodll_details($testitem){
        global $USER,$CFG;

        $itemrecord = $this->itemrecord;
        $testitem->region =$this->region;
        $testitem->cloudpoodlltoken = $this->token;
        $testitem->wwwroot=$CFG->wwwroot;
        $testitem->language=$this->language;
        $testitem->hints='';
        $testitem->owner=hash('md5',$USER->username);
        $testitem->usevoice=$itemrecord->{constants::POLLYVOICE};
        $testitem->voiceoption=$itemrecord->{constants::POLLYOPTION};

        //TT Recorder stuff
        $testitem->waveheight = 75;
        //passagehash for several reasons could rightly be empty
        //if its full it will be region|hash eg tokyo|2353531453415134545
        //we just want the hash here
        $testitem->passagehash="";
        if(!empty($itemrecord->passagehash)){
            $hashbits = explode('|',$itemrecord->passagehash,2);
            if(count($hashbits)==2){
                $testitem->passagehash  = $hashbits[1];
            }
        }

        //API gateway URL
        $testitem->asrurl = utils::fetch_lang_server_url($this->region,'transcribe');
        //recording max time
        $testitem->maxtime = 15000;

        return $testitem;
    }

    protected function dottify_text($rawtext){
        $re = '/[^\'!"#$%&\\\\\'()\*+,\-\.\/:;<=> ?@\[\\\\\]\^_`{|}~\']/u';
        $subst = '•';

        $dots = preg_replace($re, $subst, $rawtext);
        return $dots;
    }

    protected function fetch_media_files($filearea,$item){
        //get question audio div (not so easy)
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id,  constants::M_COMPONENT,$filearea,$item->questionid);
        return $files;
    }

    protected function fetch_media_urls($filearea,$item){
        //get question audio div (not so easy)
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id,  constants::M_COMPONENT,$filearea,$item->questionid);
        $urls=[];

        foreach ($files as $file) {
            $filename = $file->get_filename();

            if($filename=='.'){continue;}

            $filepath = '/';
            if($this->questionattempt){
                $mediaurl =$this->questionattempt->rewrite_pluginfile_urls('@@PLUGINFILE@@/' .
                    $file->get_filename(),
                    $file->get_component(),
                    $file->get_filearea() ,
                    $file->get_itemid());
                $urls[] = $mediaurl;
            }else {
                $mediaurl = \moodle_url::make_pluginfile_url($this->context->id, constants::M_COMPONENT,
                    $filearea, $item->id,
                    $filepath, $filename);

                $urls[] = $mediaurl->out(false);
            }

        }
        return $urls;
        // return "$this->context->id pp $filearea pp $item->id";
    }

    //public static function update_insert_item($minispeak, $data, $edit, $context, $cm ,$editoroptions, $filemanageroptions) {
    public function update_insert_item() {
        global $DB, $USER;

        $ret = new \stdClass;
        $ret->error = false;
        $ret->message = '';
        $ret->payload = null;
        $data = $this->itemrecord;

        $theitem = new \stdClass;
        $theitem->questionid = $this->moduleinstance->id;



        $theitem->type = $data->type;
        $theitem->phonetic = $data->phonetic;
        $theitem->passagehash = $data->passagehash;
        $theitem->modifiedby = $USER->id;
        $theitem->timemodified = time();

        //first insert a new item if we need to
        //that will give us a itemid, we need that for saving files
        if(isset($data->id) && !empty($data->id)){
            $theitem->id = $data->id;
        }else {

            $theitem->{constants::TEXTQUESTION} = '';
            $theitem->timecreated = time();
            $theitem->createdby = $USER->id;


            //create a rsquestionkey
            $theitem->rsquestionkey = self::create_itemkey();

            //try to insert it
            if (!$theitem->id = $DB->insert_record(constants::M_QTABLE, $theitem)) {
                $ret->error = true;
                $ret->message = "Could not insert minispeak item!";
                return $ret;
            }
        }//end of if empty($data->itemid)

        //handle all the text questions
        //if its an editor field, do this
        if (property_exists($data, constants::TEXTQUESTION . '_editor')) {
            $data = file_postupdate_standard_editor($data, constants::TEXTQUESTION, $this->editoroptions, $this->context,
                constants::M_COMPONENT, constants::TEXTQUESTION_FILEAREA, $theitem->id);
            $theitem->{constants::TEXTQUESTION} = $data->{constants::TEXTQUESTION};
            $theitem->{constants::TEXTQUESTION_FORMAT} = $data->{constants::TEXTQUESTION_FORMAT};
            //if its a text area field, do this
        } else if (property_exists($data, constants::TEXTQUESTION)) {
            $theitem->{constants::TEXTQUESTION} = $data->{constants::TEXTQUESTION};
        }

        //Question instructions
        if (property_exists($data, constants::TEXTINSTRUCTIONS)) {
            $theitem->{constants::TEXTINSTRUCTIONS} = $data->iteminstructions;
        }

        // Time limit.
        if (property_exists($data, constants::TIMELIMIT)) {
            $theitem->{constants::TIMELIMIT} = $data->{constants::TIMELIMIT};
        }

        //layout
        if (property_exists($data, constants::LAYOUT)) {
            $theitem->{constants::LAYOUT} = $data->{constants::LAYOUT};
        }else{
            $theitem->{constants::LAYOUT} = constants::LAYOUT_AUTO;
        }

        //language
        if (property_exists($data, constants::TTS_LANGUAGE)) {
            $theitem->{constants::TTS_LANGUAGE} = $data->{constants::TTS_LANGUAGE};
        }else{
            $theitem->{constants::TTS_LANGUAGE} = constants::M_LANG_ENUS;
        }

        //Item media
        //NB here we use the question id, because later it is the id of the question item that we use to fetch the file
        if (property_exists($data, constants::MEDIAQUESTION)) {
            file_save_draft_area_files($data->{constants::MEDIAQUESTION},
                $this->context->id, constants::M_COMPONENT,
                constants::MEDIAQUESTION, $theitem->questionid,
                $this->filemanageroptions);
        }

        //Item TTS
        if (property_exists($data, constants::TTSQUESTION)) {
            $theitem->{constants::TTSQUESTION} = $data->{constants::TTSQUESTION};
            if (property_exists($data, constants::TTSQUESTIONVOICE)) {
                $theitem->{constants::TTSQUESTIONVOICE} = $data->{constants::TTSQUESTIONVOICE};
            }else{
                $theitem->{constants::TTSQUESTIONVOICE} = 'Amy';
            }
            if (property_exists($data, constants::TTSQUESTIONOPTION)) {
                $theitem->{constants::TTSQUESTIONOPTION} = $data->{constants::TTSQUESTIONOPTION};
            }else{
                $theitem->{constants::TTSQUESTIONOPTION} = constants::TTS_NORMAL;
            }
            if (property_exists($data, constants::TTSAUTOPLAY)) {
                $theitem->{constants::TTSAUTOPLAY} = $data->{constants::TTSAUTOPLAY};
            }else{
                $theitem->{constants::TTSAUTOPLAY} = 0;
            }
        }

        //Item Text Area
        $edoptions = constants::ITEMTEXTAREA_EDOPTIONS;
        $edoptions['context']=$this->context;
        if (property_exists($data, constants::QUESTIONTEXTAREA . '_editor')) {
            $data->{constants::QUESTIONTEXTAREA. 'format'}=FORMAT_HTML;
            $data = file_postupdate_standard_editor($data, constants::QUESTIONTEXTAREA, $edoptions, $this->context,
                constants::M_COMPONENT, constants::TEXTQUESTION_FILEAREA, $theitem->id);
            $theitem->{constants::QUESTIONTEXTAREA} = trim($data->{constants::QUESTIONTEXTAREA});
        }

        //Item YT Clip
        if (property_exists($data, constants::YTVIDEOID)) {
            $theitem->{constants::YTVIDEOID} = $data->{constants::YTVIDEOID};
            if (property_exists($data, constants::YTVIDEOSTART)) {
                $theitem->{constants::YTVIDEOSTART} = $data->{constants::YTVIDEOSTART};
            }
            if (property_exists($data, constants::YTVIDEOEND)) {
                $theitem->{constants::YTVIDEOEND} = $data->{constants::YTVIDEOEND};
            }
        }

        //TTS Dialog
        if(property_exists($data,constants::TTSDIALOG) && $data->{constants::TTSDIALOG}!==null ){
            $theitem->{constants::TTSDIALOG} = $data->{constants::TTSDIALOG};
            $theitem->{constants::TTSDIALOGOPTS} = utils::pack_ttsdialogopts($data);
        }

        //TTS Passage
        if(property_exists($data,constants::TTSPASSAGE)&& $data->{constants::TTSPASSAGE}!==null ){
            $theitem->{constants::TTSPASSAGE} = $data->{constants::TTSPASSAGE};
            $theitem->{constants::TTSPASSAGEOPTS} = utils::pack_ttspassageopts($data);
        }

        //save correct answer if we have one
        if (property_exists($data, constants::CORRECTANSWER)) {
            $theitem->{constants::CORRECTANSWER} = $data->{constants::CORRECTANSWER};
        }

        //save correct answer if we have one
        if (property_exists($data, constants::CORRECTANSWER)) {
            $theitem->{constants::CORRECTANSWER} = $data->{constants::CORRECTANSWER};
        }

        //save text answers and other data in custom text
        //could be editor areas
        for ($anumber = 1; $anumber <= constants::MAXCUSTOMTEXT; $anumber++) {
            //if its an editor field, do this
            if (property_exists($data, constants::TEXTANSWER . $anumber . '_editor')) {
                $data = file_postupdate_standard_editor($data, constants::TEXTANSWER . $anumber, $this->editoroptions, $this->context,
                    constants::M_COMPONENT, constants::TEXTANSWER_FILEAREA . $anumber, $theitem->id);
                $theitem->{constants::TEXTANSWER . $anumber} = $data->{'customtext' . $anumber};
                $theitem->{constants::TEXTANSWER . $anumber . 'format'} = $data->{constants::TEXTANSWER . $anumber . 'format'};
                //if its a text field, do this
            } else if (property_exists($data, constants::TEXTANSWER . $anumber)) {
                $thetext = trim($data->{constants::TEXTANSWER . $anumber});
                //segment the text if it is japanese and not already segmented
                //TO DO: remove this
                /*
                if($minispeak->ttslanguage == constants::M_LANG_JAJP &&
                    ($data->type==CONSTANTS::TYPE_LISTENREPEAT ||
                        $data->type==CONSTANTS::TYPE_SPEECHCARDS ||
                        $data->type==CONSTANTS::TYPE_SHORTANSWER )){
                        if(strpos($thetext,' ')==false){
                            //  $thetext = utils::segment_japanese($thetext);
                        }
                }
                */
                $theitem->{constants::TEXTANSWER . $anumber} = $thetext;
            }
        }

        //we might have other customdata
        for ($anumber = 1; $anumber <= constants::MAXCUSTOMDATA; $anumber++) {
            if (property_exists($data, constants::CUSTOMDATA . $anumber)) {
                $theitem->{constants::CUSTOMDATA . $anumber} = $data->{constants::CUSTOMDATA . $anumber};
            }
        }

        //we might have custom int
        for ($anumber = 1; $anumber <= constants::MAXCUSTOMINT; $anumber++) {
            if (property_exists($data, constants::CUSTOMINT . $anumber)) {
                $theitem->{constants::CUSTOMINT . $anumber} = $data->{constants::CUSTOMINT . $anumber};
            }
        }


        //now update the db once we have saved files and stuff
        if (!$DB->update_record(constants::M_QTABLE, $theitem)) {
            $ret->error = true;
            $ret->message = "Could not update minispeak item!";
            return $ret;
        }else{
            $ret->item = $theitem;
            return $ret;
        }
    }//end of edit_insert_question

    public static function delete_item($questionid, $contextid)
    {
        global $DB;
        $ret = false;

        if (!$DB->delete_records(constants::M_QTABLE, array('questionid' => $questionid))) {
            print_error("Could not delete item");
            return $ret;
        }
        //remove files
        $fs = get_file_storage();

        $fileareas = constants::M_FILE_AREAS;

        foreach ($fileareas as $filearea) {
            $fs->delete_area_files($contextid, constants::M_COMPONENT, $filearea, $questionid);
        }
        $ret = true;
        return $ret;
    }


    //creates a "unique" item key so that backups and restores won't stuff things
    public static function create_itemkey()
    {
        global $CFG;
        $prefix = $CFG->wwwroot . '@';
        return uniqid($prefix, true);
    }



    /*
  * Remove any accents and chars that would mess up the transcript//passage matching
  */
    public function deaccent(){
        if($this->needs_speechrec) {
            $this->itemrecord->customtext1 = utils::remove_accents_and_poormatchchars($this->itemrecord->customtext1,$this->moduleinstance->ttslanguage);
        }
    }


    public function update_create_langmodel($olditemrecord){
        //if we need to generate a Coqui model for this, then lets do that now:
        //we want to process the hashcode and lang model if it makes sense
        $newitem = $this->itemrecord;
        if($this->needs_speechrec) {

                $passage = $newitem->customtext1;

                if (utils::needs_lang_model($this->moduleinstance,$passage)) {
                    //lets assign a default passage hash
                    if($olditemrecord) {
                        $this->itemrecord->passagehash = $olditemrecord->passagehash;
                    }else{
                        $this->itemrecord->passagehash ="";
                    }

                    //then fetch a new passage hash and see if we need to update it on the servers
                    $newpassagehash = utils::fetch_passagehash($this->language,$passage);
                    if ($newpassagehash) {
                        //check if it has changed, if its a brand new one, if so register a langmodel
                        if (!$olditemrecord || $olditemrecord->passagehash != ($this->region . '|' . $newpassagehash)) {

                            //build a lang model
                            $ret = utils::fetch_lang_model($passage, $this->language, $this->region);

                            //for doing a dry run
                            //$ret=new \stdClass();
                            //$ret->success=true;

                            if ($ret && isset($ret->success) && $ret->success) {
                                $this->itemrecord->passagehash = $this->region . '|' . $newpassagehash;
                                return true;
                            }
                        }
                    }
                }else{
                    $this->itemrecord->passagehash ='';
                }
        }else{
            $this->itemrecord->passagehash ='';
        }
        return false;
    }

    //we want to generate a phonetics if this is phonetic'able
    public function update_create_phonetic($olditemrecord){
        //if we have an old item, set the default return value to the current phonetic value
        //we will update it if the text has changed
        $newitem = $this->itemrecord;
        if($olditemrecord) {
            $thephonetics = $olditemrecord->phonetic;
        }else{
            $thephonetics ='';
        }

        if($this->needs_speechrec) {

                $newpassage = $newitem->customtext1;
                if($olditemrecord!==false) {
                    $oldpassage = $olditemrecord->customtext1;
                }else{
                    $oldpassage='';
                }

                if ($newpassage !== $oldpassage) {

                    $segmented=true;
                    $sentences=explode(PHP_EOL,$newpassage);
                    $allphonetics =[];
                    foreach($sentences as $sentence) {
                        list($thephones)  = utils::fetch_phones_and_segments($sentence, $this->language, 'tokyo', $segmented);
                        if(!empty($thephones)) {
                            $allphonetics[] = $thephones;
                        }
                    }

                    //build the final phonetics
                    if(count($allphonetics)>0) {
                        $thephonetics = implode(PHP_EOL, $allphonetics);
                    }
                }

        }
        $this->itemrecord->phonetic= $thephonetics;
        return $thephonetics;
    }

    /**
     * @param array $payloadJson
     * @param object $templateContext
     * @return void
     */
    public function export_for_answers($payloadJson, $templateContext) {
        $answerContext = new stdClass;
        $objclass = get_class($this);
        if (!empty($templateContext->hassubitems)) {
            //this should not really happen but let's handle it anyway
            if(is_null($templateContext->sentences)) {
                $templateContext->sentences = [];
            }
            for($i = 0; $i < count($templateContext->sentences); $i++) {
                $answerContext->answers[$i] = (object) ['correct' => false, 'index' => $i + 1];
                switch($objclass) {
                    case item_dictation::class:
                    case item_dictationchat::class:
                    case item_listenrepeat::class:
                    case item_speechcards::class:
                        $answerContext->answers[$i]->correctAnswer = $templateContext->sentences[$i]->sentence;
                        break;
                    case item_typinggapfill::class:
                    case item_listeninggapfill::class:
                    case item_speakinggapfill::class:
                        $maskstart = false;
                        $answerContext->answers[$i]->correctAnswer = '';
                        foreach ($templateContext->sentences[$i]->parsedstring as $charindex => $char) {
                            $char = (object) $char;
                            if ($char->type == 'text') {
                                if ($maskstart && empty($maskstart = false)) {
                                    $answerContext->answers[$i]->correctAnswer .= html_writer::end_span();
                                }
                                $answerContext->answers[$i]->correctAnswer .= $char->character;
                            } elseif ($char->type == 'mtext') {
                                $maskstart = true;
                                $answerContext->answers[$i]->correctAnswer .= $char->character;
                                $answerContext->answers[$i]->correctAnswer .= html_writer::start_span('masked-answer');
                            } elseif ($char->type == 'input') {
                                $answerContext->answers[$i]->correctAnswer .= html_writer::span($char->character);
                            }
                        }
                        if ($maskstart) {
                            $answerContext->answers[$i]->correctAnswer .= html_writer::end_span();
                        }
                        break;
                }
            }
        } else {
            switch($objclass) {
                case item_shortanswer::class:
                    $answerContext->correctAnswer = join(', ', array_column($templateContext->sentences, 'sentence'));
                    break;
                case item_multichoice::class:
                case item_multiaudio::class:
                    $answerContext->choicetype = true;
                    foreach ($templateContext->sentences as $sentence) {
                        if ($sentence->indexplusone == $templateContext->correctanswer) {
                            $answerContext->correctAnswer = $sentence->sentence;
                        }
                    }
                    break;

            }
            if (empty($payloadJson['answers'])) {
                $answerContext->answers[0] = (object) ['correct' => false];
            }
        }
        if (!empty($payloadJson['answers'])) {
            foreach ($payloadJson['answers'] as $i => $answer) {
                $answerObj = (object) $answer;
                if (isset($answerContext->answers) && empty($answerContext->answers[$i])) {
                    continue;
                }
                if (!isset($answerContext->answers)) {
                    $answerContext->answers[$i] = (object) [
                        'givenAnswer' => $answer->answer,
                        'index' => $i + 1,
                    ];
                }
                $updateObj = $answerContext->answers[$i];
                $updateObj->correct = $answerObj->correct;
                switch($objclass) {
                    case item_dictation::class:
                    case item_dictationchat::class:
                        if (isset($answerObj->answer) && $answerObj->answer !== '') {
                            $updateObj->givenAnswer = $answerObj->answer;
                        }
                        break;
                    case item_multichoice::class:
                    case item_multiaudio::class:
                        $updateObj->choices = [];
                        foreach ($templateContext->sentences as $sentence) {
                            $option = ['text' => $sentence->sentence,];
                            $option['selected'] = $sentence->indexplusone == $answerObj->answer;
                            $updateObj->choices[] = $option;
                        }
                        break;
                    case item_typinggapfill::class:
                    case item_listeninggapfill::class:
                        if (!empty($answerObj->answer) && is_array($answerObj->answer)) {
                            $updateObj->givenAnswer = '';
                            $maskstart = false;
                            $answers = array_column($answerObj->answer, 'value', 'index');
                            foreach ($templateContext->sentences[$i]->parsedstring as $charindex => $char) {
                                $char = (object) $char;
                                if ($char->type == 'text') {
                                    if ($maskstart && empty($maskstart = false)) {
                                        $updateObj->givenAnswer .= html_writer::end_span();
                                    }
                                    $updateObj->givenAnswer .= $char->character;
                                } elseif ($char->type == 'mtext') {
                                    $maskstart = true;
                                    $updateObj->givenAnswer .= $char->character;
                                    $updateObj->givenAnswer .= html_writer::start_span('masked-answer');
                                } elseif ($char->type == 'input') {
                                    $updateObj->givenAnswer .= html_writer::span(
                                        isset($answers[$charindex]) ? $answers[$charindex] : null
                                    );
                                }
                            }
                            if ($maskstart) {
                                $updateObj->givenAnswer .= html_writer::end_span();
                            }
                        }
                        break;
                }
            }
        }
        $templateContext->answerContext = $answerContext;
    }
}
