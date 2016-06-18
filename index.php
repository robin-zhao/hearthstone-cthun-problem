<?php

$game = new Game();
$game->setHits(30);
$hero = new Hero(10);

$hero->setName('英雄瓦莉拉');
$game->setHero($hero);

$master = new Master(3);

$game->addMaster($master);

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
    
    protected function speak(Character $target, $sentence)
    {
        static $number = 0;
        
        $number++;
        
        echo "\n回合" . str_pad($number, 3, ' ', STR_PAD_LEFT) . " 克苏恩 击中了" . $target->getName() 
            . "," . $target->getName() . ' ' . $sentence;
    }

    public function setHits($hit)
    {
        $this->hit = $hit;
    }

    public function setHero(Hero $hero)
    {
        $this->addCharacter($hero);
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

        if ($character instanceof Hero) {
            if ($character->dead()) {
                $this->speak($character, "死了, 游戏结束!");
                exit();
            } else {
                $this->speak($character, "剩余血量 " . $character->getHp());
            }
        } else {
            if ($character->dead()) {
                $this->dead++;
                $this->live->detach($character);
                $this->speak($character, "死了");
            } else {
                $sentence = "剩余血量 " . $character->getHp();
                if ($this->live->count() + $this->dead < $this->maxMasters + 1) {
                    $newMaster = new Master(3);
                    $this->live->attach($newMaster);
                    $sentence .= ", 呼叫了奴隶主" . $newMaster->getName();
                } else {

                }
                $this->speak($character, $sentence);
            }
        }
    }
}

abstract class Character
{
    protected $hp;
    
    protected $name;

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
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = ' [' . $name . ']';
    }
}

class Hero extends Character
{

}

class Master extends Character
{
    public function __construct($hp)
    {
        parent::__construct($hp);
        
        $this->setName('奴隶主' . substr(md5(spl_object_hash($this)), 0, 4));
    }
}
