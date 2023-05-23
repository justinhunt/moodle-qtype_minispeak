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
 * Minispeak question renderer classes.
 *
 * @package    qtype
 * @subpackage minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use \qtype_minispeak\constants;
use \qtype_minispeak\utils;

/**
 * Base class for generating the bits of output common to Minispeak
 * single and multiple questions.
 *
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_minispeak_renderer extends qtype_with_combined_feedback_renderer {



    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
      //  $response = $question->get_response($qa);
        $context = context::instance_by_id($question->contextid);
        $itemdata = utils::fetch_data_for_js($question, $context, $qa);
        $itemdata->preview=true;


        $itemshtml=[];
        $itemshtml[] = $this->render_from_template(constants::M_COMPONENT . '/' . $itemdata->type, $itemdata);

        $question_html = \html_writer::div(implode('',$itemshtml) ,constants::M_QUIZ_CONTAINER,
            array('id'=>constants::M_QUIZ_CONTAINER));

        $question_js= utils::fetch_item_amd($itemdata,$question);

        $ret =$question_html . $question_js;
        return $ret;


    }



    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    /**
     * Function returns string based on number of correct answers
     * @param array $right An Array of correct responses to the current question
     * @return string based on number of correct responses
     */
    protected function correct_choices(array $right) {
        // Return appropriate string for single/multiple correct answer(s).
        if (count($right) == 1) {
                return get_string('correctansweris', 'qtype_minispeak',
                        implode(', ', $right));
        } else if (count($right) > 1) {
                return get_string('correctanswersare', 'qtype_minispeak',
                        implode(', ', $right));
        } else {
                return "";
        }
    }

}
