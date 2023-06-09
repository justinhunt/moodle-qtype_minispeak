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
 * Minispeak question type upgrade code.
 *
 * @package    qtype
 * @subpackage minispeak
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the Minispeak question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_minispeak_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

/*
    $newversion = 2020041600;
    if ($oldversion < $newversion) {

        // Define field showstandardinstruction to be added to qtype_minispeak_options.
        $table = new xmldb_table('qtype_minispeak_options');
        $field = new xmldb_field('showstandardinstruction', XMLDB_TYPE_INTEGER, '2',
            null, XMLDB_NOTNULL, null, '1', 'shownumcorrect');

        // Conditionally launch add field showstandardinstruction.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Minispeak savepoint reached.
        upgrade_plugin_savepoint(true, $newversion, 'qtype', 'minispeak');
    }
*/


    return true;
}
