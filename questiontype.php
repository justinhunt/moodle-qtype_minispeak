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
        $options->ttslanguage= $config->ttslanguage;
        $options->itemtype=constants::TYPE_PAGE;
        $options->layout=constants::LAYOUT_AUTO;
        $options->{constants::TEXTINSTRUCTIONS}='';


        return $options;
    }

    public function save_defaults_for_new_questions(stdClass $fromform): void {
        parent::save_defaults_for_new_questions($fromform);
        //$this->set_default_value('single', $fromform->single);
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;

        //we pass in $question as itemrecord, and moduleinstance, so the signature is consistent with minilesson
        $theitem= utils::fetch_item_from_question($question,$context);
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

        }


        $this->save_hints($question, true);
        return $result;
    }

    protected function make_question_instance($questiondata) {
        question_bank::load_question_definition_classes($this->name());
        $class = 'qtype_minispeak_question';
        $q = new $class();
      //  $q->ttslanguage=get_config(constants::M_COMPONENT,'ttslanguage');
        return $q;
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        foreach (constants::M_EXTRA_FIELDS as $field) {
            $question->{$field} = $questiondata->options->{$field};
        }
        $question->itemid = $questiondata->options->id;
    }

    /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array wherer the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields() {
        $tableinfo = array(constants::M_QTABLE);
        foreach (constants::M_EXTRA_FIELDS as $field) {
            $tableinfo[] = $field;
        }
        return $tableinfo;
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
        return[];
    }



    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $itemid =false; //this will move all files, I think that is what we want
        foreach(constants::M_FILE_AREAS as $filearea) {
            $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, constants::M_COMPONENT, $filearea, $itemid);

        }

    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);

        $fs = get_file_storage();
        foreach(constants::M_FILE_AREAS as $filearea){
            $fs->delete_area_files($contextid, constants::M_COMPONENT,
                $filearea, $questionid);
        }

    }
}
