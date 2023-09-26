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

use qtype_minispeak\constants;
use qtype_minispeak\utils;
use templatable;
use renderable;

/**
 * Renderable class for a multiaudio item in a minispeak question type.
 *
 * @package    qtype_minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_multiaudio extends item {

    //the item type
    public const ITEMTYPE = constants::TYPE_MULTIAUDIO;

    /** @var bool $hassubitems indicates whether subitems or single item */
    protected $hassubitems = false;

    /**
     * The class constructor.
     *
     */
    public function __construct($question,$context){
        parent::__construct($question, $context);
        $this->needs_speechrec=true;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output) {

        $testitem= new \stdClass();
        $testitem = $this->get_common_elements($testitem);
        $testitem = $this->get_text_answer_elements($testitem);
        $testitem = $this->get_polly_options($testitem);
        $testitem = $this->set_layout($testitem);

        //sentences
        $sentences = [];
        for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
            if(!empty(trim($this->itemrecord->{constants::TEXTANSWER . $anumber}))) {
                $sentences[] = $this->itemrecord->{constants::TEXTANSWER . $anumber};
            }
        }

        //build sentence objects containing display and phonetic text
        $testitem->phonetic=$this->itemrecord->phonetic;
        if(!empty($testitem->phonetic)) {
            $phonetics = explode(PHP_EOL, $testitem->phonetic);
        }else{
            $phonetics=[];
        }
        $is_ssml=$testitem->voiceoption==constants::TTS_SSML;
        $dottify = $this->itemrecord->{constants::SHOWTEXTPROMPT}==constants::TEXTPROMPT_DOTS;
        $testitem->sentences = $this->process_spoken_sentences($sentences,$phonetics,$dottify,$is_ssml);

        //cloudpoodll
        $testitem = $this->set_cloudpoodll_details($testitem);

        return $testitem;
    }

    //overriding to get jp phonemes.
    // This is just zenkaku to hankaku for comparison of numbers
    protected function process_japanese_phonetics($sentence){
        $sentence =  mb_convert_kana($sentence,"n");
        return $sentence;
    }

    /*
 * Remove any accents and chars that would mess up the transcript//passage matching
 */
    public function deaccent(){
            $this->itemrecord->customtext1 = utils::remove_accents_and_poormatchchars($this->itemrecord->customtext1,$this->moduleinstance->ttslanguage);
            $this->itemrecord->customtext1 = utils::remove_accents_and_poormatchchars($this->itemrecord->customtext1,$this->moduleinstance->ttslanguage);
            $this->itemrecord->customtext1 = utils::remove_accents_and_poormatchchars($this->itemrecord->customtext1,$this->moduleinstance->ttslanguage);
            $this->itemrecord->customtext1 = utils::remove_accents_and_poormatchchars($this->itemrecord->customtext1,$this->moduleinstance->ttslanguage);

    }

    public function update_create_langmodel($olditemrecord){
        //if we need to generate a DeepSpeech model for this, then lets do that now:
        //we want to process the hashcode and lang model if it makes sense
        $thepassagehash ='';
        $newitem = $this->itemrecord;

            $passage = $newitem->customtext1;
            $passage .= ' ' . $newitem->customtext2;
            $passage .= ' ' . $newitem->customtext3;
            $passage .= ' ' . $newitem->customtext4;

            if (utils::needs_lang_model($this->moduleinstance,$passage)) {
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
                //if we get here just set the new passage hash to the existing one
                $this->itemrecord->passagehash =$olditemrecord->passagehash;
            }else{
                //I think this will never get here
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


            $newpassage = $newitem->customtext1;
            $newpassage .= PHP_EOL . $newitem->customtext2;
            $newpassage .= PHP_EOL . $newitem->customtext3;
            $newpassage .= PHP_EOL . $newitem->customtext4;


            if($olditemrecord!==false) {
                $oldpassage = $olditemrecord->customtext1;
                $oldpassage .= PHP_EOL . $olditemrecord->customtext2;
                $oldpassage .= PHP_EOL . $olditemrecord->customtext3;
                $oldpassage .= PHP_EOL . $olditemrecord->customtext4;

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

        $this->itemrecord->phonetic= $thephonetics;
        return $thephonetics;
    }

}
