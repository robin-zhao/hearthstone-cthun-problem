<?php

$game = new Game();
$game->setHits(30);
$game->setFace(new Face(30));
$game->addMaster(new Master(3));

$game->run();

class Game
{
    protected $maxMasters = 7;
    protected $hit = 0;

    protected $live;
    protected $dead = 0;

    protected $face;

    public function __construct()
    {
        $this->live = new SplObjectStorage();
    }

    public function setHits($hit)
    {
        $this->hit = $hit;
    }

    public function setFace(Face $face)
    {
        $this->addCharacter($face);
    }

    protected function addCharacter(Character $character)
    {
        if ($this->live->count() + $this->dead > $this->maxMasters) {
            throw new \Exception('Not possible.');
        } else {
            $this->live->attach($character);
        }
    }

    public function addMaster(Master $master)
    {
        $this->addCharacter($master);
    }

    public function run()
    {
        for ($i=0; $i<$this->hit; $i++) {
            $this->hurtRand();
        }
    }

    protected function hurtRand()
    {
        $random = rand(0,$this->live->count()-1);
        $this->live->rewind();
        for($i=0;$i<$random;$i++) {
          $this->live->next();
        }
        $character = $this->live->current();
        $character->hurt();

        if ($character instanceof Face) {
            if ($character->dead()) {
                echo "\nFace:dead, done!";
            } else {
                echo "\nFace:hit, " . $character->getHp();
            }
        } else {
            if ($character->dead()) {
                $this->dead++;
                $this->live->detach($character);
                echo "\nMaster:dead";
            } else {
                echo "\nMaster:hit, " . $character->getHp();
                if ($this->live->count() + $this->dead < $this->maxMasters + 1) {
                    $this->live->attach(new Master(3));
                    echo "\nMaster:new";
                } else {

                }
            }
        }
    }
}

abstract class Character
{
    protected $hp;

    public function __construct($hp)
    {
        $this->hp = $hp;
    }

    public function getHp()
    {
        return $this->hp;
    }

    public function hurt()
    {
        $this->hp--;
    }

    public function dead()
    {
        return $this->hp === 0;
    }
}
class Face extends Character
{

}

class Master extends Character
{

}
