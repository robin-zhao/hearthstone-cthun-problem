<?php

$live = $dead = 0;

for ($i=0; $i<100000; $i++) {

    $game = new Game();
    $game->setHits(20);
    $hero = new Hero(10);

    $hero->setName('英雄瓦莉拉');
    $game->setHero($hero);

    $game->addMaster(new Master(3));

    $game->run();
    
    if ($game->isHeroDead()) {
        $dead++;
    } else {
        $live++;
    }
    
    unset($hero);
    unset($game);

}

echo "存活" . $live . ' vs ' . '死亡' . $dead;

class Game
{
    /**
     * Max numbers of masters.
     * @var int
     */
    protected $maxMasters = 7;
    
    /**
     * C'thun's attack
     * @var int 
     */
    protected $hit = 0;

    /**
     * Contains characters.
     * @var SplObjectStorage
     */
    protected $live;
    
    /**
     * How many dead masters;
     * @var int
     */
    protected $dead = 0;

    /**
     * Whether hero is dead.
     * @var boolean
     */
    protected $heroDead = false;

    public function __construct()
    {
        $this->live = new SplObjectStorage();
    }
    
    public function __destruct()
    {
        unset($this->live);
    }
    
    public function isHeroDead()
    {
        return $this->heroDead;
    }
    
    protected function speak(Character $target, $sentence)
    {
        static $number = 0;
        
        $number++;
        
//        echo "\n回合" . str_pad($number, 3, ' ', STR_PAD_LEFT) . " 克苏恩 击中了" . $target->getName() 
//            . "," . $target->getName() . ' ' . $sentence;
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
            if ($this->heroDead) {
                break;
            }
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
                $this->heroDead = true;
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
