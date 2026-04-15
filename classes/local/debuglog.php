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
 * Debugging class
 *
 * @package   local_recertify
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\local;

defined('MOODLE_INTERNAL') || die();

class debuglog {
    public static function add($msg, $showusage = false) {
        // Remove the '//' from the start of the next line to turn off all debugging
        // return;

        global $CFG;
        static $starttime = null;
        if (is_null($starttime)) {
            $starttime = microtime(true);
        }

        $fp = fopen($CFG->dataroot . '/debug.log', 'a');

        if (!$fp) {
            return;
        }

        $usage = '';
        if ($showusage) {
            $memory = sprintf('%.2f', (memory_get_usage() / (1024.0 * 1024.0))) . 'M';
            $peak = sprintf('%.2f', (memory_get_peak_usage() / (1024.0 * 1024.0))) . 'M';
            $time = sprintf('%.1f', microtime(true) - $starttime) . 's';
            $usage = " - memory: {$memory} (peak: {$peak}) time: {$time}";
        }

        fwrite($fp, date('j M Y H:i:s') . ' - ' . $msg . $usage . "\n");
        fclose($fp);
    }

    public static function usage($info = null) {
        global $CFG, $SESSION;
        static $logon = null;
        if ($logon === null) {
            if (optional_param('startdebuglog', false, PARAM_BOOL)) {
                $SESSION->debuglog = true;
            } else if (optional_param('stopdebuglog', false, PARAM_BOOL)) {
                $SESSION->debuglog = false;
            }
            $logon = optional_param('debuglog', false, PARAM_BOOL);
            if (!$logon) {
                $logon = !empty($SESSION->debuglog);
            }
        }
        if (!$logon) {
            return;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $lastline = array_shift($backtrace);
        $filename = str_replace($CFG->dirroot, '', $lastline['file']);

        $msg = $filename . ' (' . $lastline['line'] . ')';
        if ($info) {
            $msg .= ' ' . $info;
        }

        self::add($msg, true);
    }

    public static function var_dump($var) {
        ob_start();
        print_r($var);
        $out = ob_get_clean();

        self::add($out);
    }

    public static function backtrace() {
        $output = "Backtrace: \n";
        $trace = debug_backtrace();
        foreach ($trace as $depth => $details) {
            $output .= $depth . ': ';
            $output .= $details['file'] . ' - ';
            $output .= 'line ' . $details['line'] . ': ';
            if ($details['function']) {
                $output .= $details['function'] . '()';
            }
            $output .= "\n";
        }
        self::add($output);
    }

    public static function output(): void {
        global $CFG;
        $filename = $CFG->dataroot . '/debug.log';
        if (!file_exists($filename)) {
            echo "No log found";
            return;
        }
        echo '<pre>';
        readfile($filename);
        echo '</pre>';
    }

    public static function clear(): void {
        global $CFG;
        $filename = $CFG->dataroot . '/debug.log';
        @unlink($filename);
    }
}
