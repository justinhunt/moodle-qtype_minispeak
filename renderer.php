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
class qtype_minispeak_renderer extends qtype_renderer {



    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
      //  $response = $question->get_response($qa);
        $context = context::instance_by_id($question->contextid);
        $itemdata = utils::fetch_data_for_js($question, $context, $qa);
        $itemdata->locked = !empty($options->readonly);
        $questiontype = $itemdata->questiontype;
        unset($itemdata->questiontype);
        $payload = $qa->get_last_qt_var('payload');

        //this will tell it to not do NEXT screen (ie to just finish)
        $payloadfield = $qa->get_qt_field_name('payload');
        $itemdata->singlemode=true;
        $itemdata->payloadfield = str_replace(':', '_', $payloadfield);
        $itemdata->qubaid = $qa->get_usage_id() . ',' . $qa->get_slot();

        if ($itemdata->locked) {
            $itemdata->answerContext = [];
            $payloadJson = utils::decode_payload($payload, $qa->get_last_qt_var(constants::TOKENKEYPRIVATE));
            $questiontype->export_for_answers((array) $payloadJson, $itemdata);
        }

        $itemshtml=[];
        $itemshtml[] = $this->render_from_template(constants::M_COMPONENT . '/' . $itemdata->type, $itemdata);

        $question_html = \html_writer::div(implode('',$itemshtml) ,constants::M_QUIZ_CONTAINER,
            array('id'=>constants::M_QUIZ_CONTAINER));

        $question_js= utils::fetch_item_amd($itemdata,$question);

        $ret = html_writer::tag('div', $question->format_questiontext($qa),
            array('class' => 'qtext'));

        $ret .= $question_html . $question_js;
        $ret .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => $payloadfield, 'id' => $itemdata->payloadfield,
                'value' => $payload]);

        return $ret;
    }

}
