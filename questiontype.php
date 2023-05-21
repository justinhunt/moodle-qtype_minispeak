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
 * The questiontype class for the Minispeak question type.
 *
 * @package    qtype
 * @subpackage minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');

use \qtype_minispeak\constants;
use \qtype_minispeak\utils;


/**
 * The Minispeak question type.
 *
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_minispeak extends question_type {
    public function get_question_options($question) {
        global $DB, $OUTPUT;

        $question->options = $DB->get_record('qtype_minispeak_options', ['questionid' => $question->id]);

        if ($question->options === false) {
            // If this has happened, then we have a problem.
            // For the user to be able to edit or delete this question, we need options.
            debugging("Question ID {$question->id} was missing an options record. Using default.", DEBUG_DEVELOPER);

            $question->options = $this->create_default_options($question);
        }

        parent::get_question_options($question);
    }

    /**
     * Create a default options object for the provided question.
     *
     * @param object $question The queston we are working with.
     * @return object The options object.
     */
    protected function create_default_options($question) {
        // Create a default question options record.
        $options = new stdClass();
        $options->questionid = $question->id;

        $config = get_config('qtype_minispeak');
        $options->transcriber = $config->transcriber;
        $options->containerwidth= $config->containerwidth;
        $options->itemtype=constants::TYPE_PAGE;
        $options->layout=constants::LAYOUT_AUTO;
        $options->{constants::TEXTINSTRUCTIONS}='';


        // Get the default strings and just set the format.
        /*
        $options->correctfeedback = get_string('correctfeedbackdefault', 'question');
        $options->correctfeedbackformat = FORMAT_HTML;
        $options->partiallycorrectfeedback = get_string('partiallycorrectfeedbackdefault', 'question');;
        $options->partiallycorrectfeedbackformat = FORMAT_HTML;
        $options->incorrectfeedback = get_string('incorrectfeedbackdefault', 'question');
        $options->incorrectfeedbackformat = FORMAT_HTML;
*/
        return $options;
    }

    public function save_defaults_for_new_questions(stdClass $fromform): void {
        parent::save_defaults_for_new_questions($fromform);
        //$this->set_default_value('single', $fromform->single);
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        //TO DO probably just remove this
        $questioninstance = false;


        $theitem= utils::fetch_item_from_itemrecord($question,$question,$context);
        //TO DO fix up $olditem
        $olditem=false;

        //remove bad accents and things that mess up transcription (kind of like clear but permanent)
        $theitem->deaccent();

        //create passage hash
        $theitem->update_create_langmodel($olditem);

        //lets update the phonetics
        $theitem->update_create_phonetic($olditem);

        $result = $theitem->update_insert_item();
        if($result->error==true){
            print_error($result->message);
            //what to do?

        }else{
            $theitem=$result->item;
        }

/*
        $options = $DB->get_record('qtype_minispeak_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->showstandardinstruction = 0;
            $options->id = $DB->insert_record('qtype_minispeak_options', $options);
        }


        if (isset($question->layout)) {
            $options->layout = $question->layout;
        }
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->showstandardinstruction = !empty($question->showstandardinstruction);
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_minispeak_options', $options);
        */

        $this->save_hints($question, true);
        return $result;
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        $class = 'qtype_minispeak_question';
        return new $class();
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata, true);

    }

    public function make_answer($answer) {
        // Overridden just so we can make it public for use by question.php.
        return parent::make_answer($answer);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        qtype_minispeak\local\itemtype\item::delete_item($questionid,$contextid);
        parent::delete_question($questionid, $contextid);
    }

    public function get_random_guess_score($questiondata) {
        return null;
    }

    public function get_possible_responses($questiondata) {
        if ($questiondata->options->single) {
            $responses = array();

            foreach ($questiondata->options->answers as $aid => $answer) {
                $responses[$aid] = new question_possible_response(
                        question_utils::to_plain_text($answer->answer, $answer->answerformat),
                        $answer->fraction);
            }

            $responses[null] = question_possible_response::no_response();
            return array($questiondata->id => $responses);
        } else {
            $parts = array();

            foreach ($questiondata->options->answers as $aid => $answer) {
                $parts[$aid] = array($aid => new question_possible_response(
                        question_utils::to_plain_text($answer->answer, $answer->answerformat),
                        $answer->fraction));
            }

            return $parts;
        }
    }



    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }
}
