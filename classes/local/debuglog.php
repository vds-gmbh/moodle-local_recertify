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
 * Debug logging utility for development and troubleshooting.
 *
 * @package    local_recertify
 * @copyright  2023 Synergy Learning
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_recertify\local;

/**
 * Debug logging utility for development and troubleshooting.
 *
 * @package    local_recertify
 * @copyright  2023 Synergy Learning
 * @copyright  2026 onwards VdS Schadenverhütung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debuglog {
    /**
     * Write a message to the debug log file.
     *
     * @param string $msg The message to log.
     * @param bool $showusage Whether to append memory and timing info.
     * @return void
     */
    public static function add($msg, $showusage = false) {
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

    /**
     * Log memory and timing usage information if debug logging is active.
     *
     * @param string|null $info Optional context label.
     * @return void
     */
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

    /**
     * Dump a variable to the debug log.
     *
     * @param mixed $var The variable to dump.
     * @return void
     */
    public static function dump($var) {
        self::add(var_export($var, true));
    }

    /**
     * Write a full backtrace to the debug log file.
     *
     * @return void
     */
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

    /**
     * Output the debug log contents to the browser.
     *
     * @return void
     */
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

    /**
     * Delete the debug log file.
     *
     * @return void
     */
    public static function clear(): void {
        global $CFG;
        $filename = $CFG->dataroot . '/debug.log';
        @unlink($filename);
    }
}
