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
 * Minispeak question definition classes.
 *
 * @package    qtype
 * @subpackage minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

use \qtype_minispeak\constants;
use qtype_minispeak\utils;

class qtype_minispeak_question extends question_graded_automatically {

    protected $secret;

    public function start_attempt(question_attempt_step $step, $variant) {
        if (!$step->has_qt_var(constants::TOKENKEYPRIVATE)) {
            $step->set_qt_var(constants::TOKENKEYPRIVATE, complex_random_string(25));
        }
    }

    public function apply_attempt_state(question_attempt_step $step) {
        if ($step->has_qt_var(constants::TOKENKEYPRIVATE)) {
            $this->secret = $step->get_qt_var(constants::TOKENKEYPRIVATE);
        }
    }

    public function get_expected_data() {
        return array('payload' => PARAM_RAW_TRIMMED);
    }

    public function get_correct_response() {
        return array();
    }

    public function summarise_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return null;
        }
        list($fraction) = $this->grade_response($response);
        if ($fraction == 1) {
            return get_string('fullycompleted', 'qtype_minispeak');
        } else {
            return get_string('partialcompleted', 'qtype_minispeak');
        }
    }

    public function classify_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return array($this->id => question_classified_response::no_response());
        }
        list($fraction) = $this->grade_response($response);
        if ($fraction == 1) {
            return array($this->id => new question_classified_response(1,
                    get_string('fullycompleted', 'qtype_minispeak'), $fraction));
        } else {
            return array($this->id => new question_classified_response(0,
                    get_string('partialcompleted', 'qtype_minispeak'), $fraction));
        }
    }

    public function is_complete_response(array $response) {
        return true;
    }

    public function is_full_response(array $response) {
        if (empty($response['payload'])) {
            return false;
        }
        $payload = utils::decode_payload($response['payload'], $this->secret);
        if (!empty($payload) && array_key_exists('grade', $payload)) {
            return true;
        }
        return false;
    }

    public function get_validation_error(array $response) {
        if ($this->is_full_response($response)) {
            return '';
        }
        return get_string('pleaseselectananswer', 'qtype_minispeak');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'payload');
    }

    public function grade_response(array $response) {
        $fraction = 0;
        $payload = !empty($response['payload']) ? $response['payload'] : null;
        $payload = utils::decode_payload($payload, $this->secret);
        if (!empty($payload) && !empty($payload['grade'])) {
            $fraction = format_float($payload['grade'] / 100, 2);
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == constants::M_COMPONENT && in_array($filearea, constants::M_FILE_AREAS)) {
            return true;
        } else {
            return parent::check_file_access($qa, $options, $component,
                $filearea, $args, $forcedownload);
        }
    }

    public function get_question_definition_for_external_rendering(question_attempt $qa, question_display_options $options) {
        // No need to return anything, external clients do not need additional information for rendering this question type.
        return null;
    }
}
