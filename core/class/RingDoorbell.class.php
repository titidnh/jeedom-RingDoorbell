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
        if (exec('pip3 list | grep ring-doorbell | wc -l') == 0)
        {
            $return['state'] = 'nok';
        }

        return $return;
    }

    public static function dependancy_install() {
        log::remove(__CLASS__ . '_update');
        return array('script' => dirname(__FILE__) . '/../../resources/install.sh ' . jeedom::getTmpFolder('RingDoorbell') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

    public static function syncWithRing() {
        log::add(__CLASS__, 'debug', "Sync with Ring.com started.");
        $result = shell_exec('sudo python3 '.dirname(__FILE__) . '/../../resources/RingDoorbellSync.py -u '. config::byKey('username', 'RingDoorbell') .' -p \''. config::byKey('password', 'RingDoorbell').'\'');
        log::add(__CLASS__, 'debug', "Values received from Ring: ".$result);
        $splittedDoorbells = explode(PHP_EOL, $result);
        foreach ($splittedDoorbells as $doorbell) {
            $values = explode('||', $doorbell);
            if(count($values) == 3){
                log::add(__CLASS__, 'debug', "Ring doorbell: ".$doorbell);
                $eqLogic = eqLogic::byLogicalId($values[0], 'RingDoorbell');
                if (!is_object($eqLogic)) {
                    $eqLogic = new RingDoorbell();
                    $eqLogic->setLogicalId($values[0]);
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setCategory('other', 1);
                    $eqLogic->setIsVisible(1);
                }

                $eqLogic->setName($values[2]);
                $eqLogic->save();
            }
        }
    }

    public static function cron5() {

    }
}

class RingDoorbellCmd extends cmd {
    public function execute($_options = array()) {
    }
}