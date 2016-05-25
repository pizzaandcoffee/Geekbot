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

include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/System/Utils.php';
include __DIR__ . '/System/Settings.php';
include __DIR__ . '/System/Commands.php';
include __DIR__ . '/System/Database.php';
include __DIR__ . '/System/Reactions.php';
include __DIR__ . '/System/Stats.php';
include __DIR__ . '/Commands/commandInterface.php';

use Discord\Discord;
use Discord\WebSockets\WebSocket;
use Geekbot\CommandsContainer;

$version = "2.0 alpha";

echo("  ____ _____ _____ _  ______   ___ _____\n");
echo(" / ___| ____| ____| |/ / __ ) / _ \\_   _|\n");
echo("| |  _|  _| |  _| | ' /|  _ \\| | | || |\n");
echo("| |_| | |___| |___| . \\| |_) | |_| || |\n");
echo(" \\____|_____|_____|_|\\_\\____/ \\___/ |_|\n");

echo "{$version}\n\n";

class Bot {
    private $discord;
    public $ws;
    private $commands;
    private $reactions;
    
    function __construct() {

        $this->discord = new Discord(\Geekbot\Settings::envGet('token'));
        $this->ws = new WebSocket($this->discord);
        $this->commands = new CommandsContainer();
        $this->reactions = new Geekbot\Reactions(); 


        date_default_timezone_set('Europe/Amsterdam');
        
        $this->initSocket();
    }
    
    
    function initSocket() {
        $this->ws->on('ready', function ($discord){
            $discord->updatePresence($this->ws, \Geekbot\Settings::envGet('playing'), 0);
            echo "geekbot is ready!\n" . PHP_EOL;

            $this->ws->on('message', function ($message) {
                
                $stats = new \Geekbot\Stats($message);

                $command = \Geekbot\Utils::getCommand($message);
                if($this->commands->commandExists($command)){
                    $commandslist = $this->commands->getCommands();
                    if(in_array('Geekbot\Commands\basicCommand', class_implements($this->commands->getCommand($command)))) {
                        $message->reply($commandslist[$command]->runCommand());
                    } else if(in_array('Geekbot\Commands\messageCommand', class_implements($this->commands->getCommand($command)))) {
                        $result = $commandslist[$command]->runCommand($message);
                        if(is_string($result)){
                            $message->reply($result);
                        } else {
                            $message = $result;
                        }
                    }
                }
                else {
                    $reaction = $this->reactions->getReaction(\Geekbot\Utils::getCommand($message), $message);
                    if( $reaction != NULL) {
                        $message->channel->sendMessage($reaction);
                    }
                }

                $reply = $message->timestamp->format('d/m/y H:i:s') . ' - ';
                $reply .= $message->author->username . ' - ';
                $reply .= $message->content;
                echo $reply . PHP_EOL;
            });
        }
        );

        $this->ws->on('error', function ($error, $ws) {
            print($error);
        });
    }
   
    function run(){
        $this->ws->run();
    }
}

$bot = new Bot();
$bot->run();
