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

/**
 * Base class for Minispeak questions. The parts that are common to
 * single select and multiple select.
 *
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_minispeak_base extends question_graded_automatically {
    const LAYOUT_DROPDOWN = 0;
    const LAYOUT_VERTICAL = 1;
    const LAYOUT_HORIZONTAL = 2;

    public $answers;

    public $shuffleanswers;
    public $answernumbering;
    /**
     * @var int standard instruction to be displayed if enabled.
     */
    public $showstandardinstruction = 0;
    public $layout = self::LAYOUT_VERTICAL;
/*
    public $correctfeedback;
    public $correctfeedbackformat;
    public $partiallycorrectfeedback;
    public $partiallycorrectfeedbackformat;
    public $incorrectfeedback;
    public $incorrectfeedbackformat;
*/
    protected $order = null;

    public function start_attempt(question_attempt_step $step, $variant) {
        /*
        $this->order = array_keys($this->answers);
        if ($this->shuffleanswers) {
            shuffle($this->order);
        }
        $step->set_qt_var('_order', implode(',', $this->order));
        */
    }

    public function apply_attempt_state(question_attempt_step $step) {
        /*
        $this->order = explode(',', $step->get_qt_var('_order'));

        // Add any missing answers. Sometimes people edit questions after they
        // have been attempted which breaks things.
        foreach ($this->order as $ansid) {
            if (isset($this->answers[$ansid])) {
                continue;
            }
            $a = new stdClass();
            $a->id = 0;
            $a->answer = html_writer::span(get_string('deletedchoice', 'qtype_minispeak'),
                    'notifyproblem');
            $a->answerformat = FORMAT_HTML;
            $a->fraction = 0;
            $a->feedback = '';
            $a->feedbackformat = FORMAT_HTML;
          //  $this->answers[$ansid] = $this->qtype->make_answer($a);
            $this->answers[$ansid]->answerformat = FORMAT_HTML;
        }]*/
    }

    public function validate_can_regrade_with_other_version(question_definition $otherversion): ?string {
        $basemessage = parent::validate_can_regrade_with_other_version($otherversion);
        if ($basemessage) {
            return $basemessage;
        }

        if (count($this->answers) != count($otherversion->answers)) {
            return get_string('regradeissuenumchoiceschanged', 'qtype_minispeak');
        }

        return null;
    }

    public function update_attempt_state_data_for_new_version(
            question_attempt_step $oldstep, question_definition $otherversion) {
        $startdata = parent::update_attempt_state_data_for_new_version($oldstep, $otherversion);

        $mapping = array_combine(array_keys($otherversion->answers), array_keys($this->answers));

        $oldorder = explode(',', $oldstep->get_qt_var('_order'));
        $neworder = [];
        foreach ($oldorder as $oldid) {
            $neworder[] = $mapping[$oldid] ?? $oldid;
        }
        $startdata['_order'] = implode(',', $neworder);

        return $startdata;
    }

    public function get_question_summary() {
        $question = $this->html_to_text($this->questiontext, $this->questiontextformat);
        $choices = array();

        return $question . ': ' . implode('; ', $choices);
    }

    public function get_order(question_attempt $qa) {
        $this->init_order($qa);
        return $this->order;
    }

    protected function init_order(question_attempt $qa) {
        if (is_null($this->order)) {
            $this->order = explode(',', $qa->get_step(0)->get_qt_var('_order'));
        }
    }

    public abstract function get_response(question_attempt $qa);

    public abstract function is_choice_selected($response, $value);

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && in_array($filearea,
                array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'))) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea, $args);

        } else if ($component == 'question' && $filearea == 'answer') {
            $answerid = reset($args); // Itemid is answer id.
            return  in_array($answerid, $this->order);

        } else if ($component == 'question' && $filearea == 'answerfeedback') {
            $answerid = reset($args); // Itemid is answer id.
            $response = $this->get_response($qa);
            $isselected = false;
            foreach ($this->order as $value => $ansid) {
                if ($ansid == $answerid) {
                    $isselected = $this->is_choice_selected($response, $value);
                    break;
                }
            }
            // Param $options->suppresschoicefeedback is a hack specific to the
            // oumultiresponse question type. It would be good to refactor to
            // avoid refering to it here.
            return $options->feedback && empty($options->suppresschoicefeedback) &&
                    $isselected;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    /**
     * Return the question settings that define this question as structured data.
     *
     * @param question_attempt $qa the current attempt for which we are exporting the settings.
     * @param question_display_options $options the question display options which say which aspects of the question
     * should be visible.
     * @return mixed structure representing the question settings. In web services, this will be JSON-encoded.
     */
    public function get_question_definition_for_external_rendering(question_attempt $qa, question_display_options $options) {
        // This is a partial implementation, returning only the most relevant question settings for now,
        // ideally, we should return as much as settings as possible (depending on the state and display options).

        return [
            'shuffleanswers' => $this->shuffleanswers,
            'answernumbering' => $this->answernumbering,
            'showstandardinstruction' => $this->showstandardinstruction,
            'layout' => $this->layout,
        ];
    }
}


/**
 * Represents a Minispeak question where only one choice should be selected.
 *
 * TO DO -  this is BS, just so the page will load, not started coding yet
 *
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_minispeak_question extends qtype_minispeak_base {
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_minispeak', 'question');
    }

    public function get_min_fraction() {
        $minfraction = 0;


        return $minfraction;
    }

    /**
     * Return an array of the question type variables that could be submitted
     * as part of a question of this type, with their types, so they can be
     * properly cleaned.
     * @return array variable name => PARAM_... constant.
     */
    public function get_expected_data() {
        return array('answer' => PARAM_INT);
    }

    public function summarise_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return null;
        }
        $answerid = $this->order[$response['answer']];
        return $this->html_to_text($this->answers[$answerid]->answer,
                $this->answers[$answerid]->answerformat);
    }

    public function un_summarise_response(string $summary) {
        foreach ($this->order as $key => $answerid) {
            if ($summary === $this->html_to_text($this->answers[$answerid]->answer,
                    $this->answers[$answerid]->answerformat)) {
                return ['answer' => $key];
            }
        }
        return [];
    }

    public function classify_response(array $response) {
        if (!$this->is_complete_response($response)) {
            return array($this->id => question_classified_response::no_response());
        }
        $choiceid = $this->order[$response['answer']];
        $ans = $this->answers[$choiceid];
        return array($this->id => new question_classified_response($choiceid,
                $this->html_to_text($ans->answer, $ans->answerformat), $ans->fraction));
    }

    public function get_correct_response() {

        return array();
    }

    public function prepare_simulated_post_data($simulatedresponse) {
        $ansid = 0;
        foreach ($this->answers as $answer) {
            if (clean_param($answer->answer, PARAM_NOTAGS) == $simulatedresponse['answer']) {
                $ansid = $answer->id;
            }
        }
        if ($ansid) {
            return array('answer' => array_search($ansid, $this->order));
        } else {
            return array();
        }
    }

    public function get_student_response_values_for_simulation($postdata) {
        if (!isset($postdata['answer'])) {
            return array();
        } else {
            $answer = $this->answers[$this->order[$postdata['answer']]];
            return array('answer' => clean_param($answer->answer, PARAM_NOTAGS));
        }
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        if (!$this->is_complete_response($prevresponse)) {
            $prevresponse = [];
        }
        if (!$this->is_complete_response($newresponse)) {
            $newresponse = [];
        }
        return question_utils::arrays_same_at_key($prevresponse, $newresponse, 'answer');
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) && $response['answer'] !== ''
                && (string) $response['answer'] !== '-1';
    }

    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    public function grade_response(array $response) {
        if (array_key_exists('answer', $response) &&
                array_key_exists($response['answer'], $this->order)) {
            $fraction = $this->answers[$this->order[$response['answer']]]->fraction;
        } else {
            $fraction = 0;
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseselectananswer', 'qtype_minispeak');
    }

    public function get_response(question_attempt $qa) {
        return $qa->get_last_qt_var('answer', -1);
    }

    public function is_choice_selected($response, $value) {
        return (string) $response === (string) $value;
    }
}
