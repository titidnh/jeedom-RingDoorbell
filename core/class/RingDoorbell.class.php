<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class RingDoorbell extends eqLogic {
    public static function dependancy_info() {
        $return = array();
        $return['log'] = __CLASS__ . '_update';
        $return['progress_file'] = jeedom::getTmpFolder('RingDoorbell') . '/dependance';
        $return['state'] = 'ok';
        if (exec('pip3 list | grep ring_doorbell | wc -l'))
        {
            $return['state'] = 'nok';
        }

        return $return;
    }
    public static function dependancy_install() {
        log::remove(__CLASS__ . '_update');
        return array('script' => dirname(__FILE__) . '/../../resources/install.sh ' . jeedom::getTmpFolder('RingDoorbell') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }
}

class RingDoorbellCmd extends cmd {
    public function execute($_options = array()) {
    }
}