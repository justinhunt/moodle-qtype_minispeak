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
 * Renderable class for a smartframe item in a minispeak question type.
 *
 * @package    qtype_minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_smartframe extends item {

    //the item type
    public const ITEMTYPE = constants::TYPE_SMARTFRAME;

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output){
        global $USER;

        $testitem= new \stdClass();
        $testitem = $this->get_common_elements($testitem);
        $testitem = $this->get_text_answer_elements($testitem);
        $testitem = $this->get_polly_options($testitem);
        $testitem = $this->set_layout($testitem);

        $testitem->smartframehost='';
        if(!empty($testitem->customtext1)) {
            $hostbits = parse_url($testitem->customtext1);
            if($hostbits) {
                $testitem->smartframehost = $hostbits['scheme'] . "://" . $hostbits['host'];
            }
            //if username is requested, could set it here, any -usersname- in iframe url will be replaced with url encoded name
            //as test use this url in smartframe instance  [site root]/qtype/minispeak/framemessagetest.html?someid=1234&usersname=-usersname-
            $users_name = fullname($USER);
            $testitem->customtext1 = str_replace('-usersname-',urlencode($users_name),$testitem->customtext1);
        }

        return $testitem;
    }

}
