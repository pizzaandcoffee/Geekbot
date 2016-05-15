<?php

/*
 *   This file is part of Geekbot.
 *
 *   Geekbot is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Geekbot is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Geekbot.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Geekbot;

class KeyStorage
{
    private function check(){
        if(!file_exists(__DIR__.'/db')){
            mkdir(__DIR__.'/db');
        }
        if(!file_exists(__DIR__.'/db/db.json')){
            fopen(__DIR__.'/db/db.json', 'w');
        }
        return true;
    }

    public function put($name, $data){
        $where = __DIR__.'/db/db.json';
        if($this->check()) {
            $get_settings = file_get_contents($where);
            $decode_settings = json_decode($get_settings);
            $decode_settings->{$name} = $data;
            $new = json_encode($decode_settings);
            file_put_contents($where, $new);
        }
    }

    public function get($name){
        $where = __DIR__.'/db/db.json';
        $get_settings = file_get_contents($where);
        $decode_settings = json_decode($get_settings);
        try {
            $value = $decode_settings->{$name};
            return $value;
        }
        catch(Exception $e){
            return 0;
        }
    }
}