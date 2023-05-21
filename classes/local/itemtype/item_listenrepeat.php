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
 * Renderable class for a listenrepeat item in a minispeak question type.
 *
 * @package    qtype_minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_listenrepeat extends item {

    //the item type
    public const ITEMTYPE = constants::TYPE_LISTENREPEAT;


    /**
     * The class constructor.
     *
     */
    public function __construct($itemrecord, $moduleinstance=false, $context=false){
        parent::__construct($itemrecord, $moduleinstance, $context);
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
        if(isset($testitem->customtext1)) {
            $sentences = explode(PHP_EOL, $testitem->customtext1);
        }
        //build sentence objects containing display and phonetic text
        $testitem->phonetic=$this->itemrecord->phonetic;
        if(!empty($testitem->phonetic)) {
            $phonetics = explode(PHP_EOL, $testitem->phonetic);
        }else{
            $phonetics=[];
        }
        $testitem->sentences = $this->process_spoken_sentences($sentences,$phonetics);

        //cloudpoodll
        $testitem = $this->set_cloudpoodll_details($testitem);


        return $testitem;
    }

    //overriding to get jp phonemes
    //If this is Japanese and a'chat' activity, the display sentence will be read as is
    // but the sentence we show on screen as the students entry needs to be broken into "words"
    //so we process it. In listen and speak it still shows the target, so its word'ified.
    //speechcards we do not give word level feedback. so we do nothing special
    //key point is to pass unwordified passage to compare_passage_transcipt ajax.
    protected function process_japanese_phonetics($sentence){
        // sadly this segmentation algorithm mismatches with server based one we need for phonetics
        //so we are not using it. We ought to save the segment rather than call each time
        // 初めまして =>(1) はじめまし て　＆　(2) はじめま　して
        //はなしてください=>(1)はな　して　く　だ　さい & (2)はな　して　ください
        //  $sentence = utils::segment_japanese($sentence);
        //TO DO save segments and not collect them at runtime
        list($phones,$sentence) = utils::fetch_phones_and_segments($sentence,$this->moduleinstance->ttslanguage,$this->moduleinstance->region);
        return $sentence;
    }

}
