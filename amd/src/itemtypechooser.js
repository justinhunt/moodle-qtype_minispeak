// This file is part of Moodle - http://moodle.org/ //
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

import {get_string as getString} from 'core/str';
import {prefetchStrings} from 'core/prefetch';
/**
 * Mini Speak Type Choose
 *
 * @module     qtype_minispeak/typechooser
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      4.0
 */

const Selectors = {
    fields: {
        selector: '[data-itemtypechooser-field="selector"]',
        updateButton: '[data-itemtypechooser-field="updateButton"]',
        typeInstructions: '[data-itemtypechooser-field="typeInstructions"]'
    },
};

/**
 * Initialise the format chooser.
 */
export const init = () => {

    prefetchStrings('qtype_minispeak', [
        'multiaudio_instructions1',
        'multichoice_instructions1',
        'shortaudio_instructions1',
        'smartframe_instructions1',
        'listenrepeat_instructions1',
        'dictation_instructions1',
        'dictationchat_instructions1',
        'speakinggapfill_instructions1',
        'typinggapfill_instructions1',
        'listeninggapfill_instructions1',
        'comprehensionquiz_instructions1',
        'buttonquiz_instructions1',
        'page_instructions1'

    ]);

    document.querySelector(Selectors.fields.selector).addEventListener('change', e => {
        const form = e.target.closest('form');
        const updateButton = form.querySelector(Selectors.fields.updateButton);
        const typeInstructions = form.querySelector(Selectors.fields.typeInstructions);
        const fieldset = updateButton.closest('fieldset');

        //set the form to update
        const url = new URL(form.action);
        url.hash = fieldset.id;
        form.action = url.toString();

        // Set the instructions for the selected type.
        getString(e.target.value +'_instructions1', 'qtype_minispeak')
            .then(function(theinstructions){
                typeInstructions.value=theinstructions;
                updateButton.click();
            }
        );
    });
};
