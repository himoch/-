<?php
namespace player;

class player {
    protected $name;
    protected $side;
    protected $role;
    protected $order;
    protected $hasLady;
    protected $checkedByLady;
    protected $isQuestMember;

    public function __construct($name){
        $this->name = $name;
        $this->hasLady = false;
        $this->checkedByLady = false;
        $this->isQuestMember = false;
    }

    public function giveSide($side){
        $this->side = $side;
    }

    public function giveRole($role){
        $this->role = $role;
    }

    public function setOrder($order){
        $this->order = $order;
    }

    public function getOrder(){
        return $this->order;
    }

    public function getNmae(){
        return $this->name;
    }

    public function isMerlin($last = false){
        if($last){
            return $this->role == 'Merlin';
        }else{
            return ($this->role == 'Merlin' || $this->role == 'Morgan');
        }
    }

    public function isDark($isMerlin){
        if(!$isMerlin && $this->role == 'Oberon'){
            return false;
        }elseif($isMerlin && $this->role == 'Mordred'){
            return false;
        }else{
            return ($this->side == 'dark');
        }
    }

    public function getName(){
        return $this->name;
    }

    public function getSide(){
        return $this->side;
    }

    public function getRole($forCheck=false){
        if($forCheck){
            if($this->role){
                return $this->role;
            }else{
                return $this->side;
            }
        }else{
            return $this->role;
        }
    }

    public function isLeader($order){
        if($this->order == $order){
            return true;
        }else{
            return false;
        }
    }

    public function isMurder(){
        if($this->role == 'murder'){
            return true;
        }else{
            return false;
        }
    }

    public function giveLady(){
        $this->checkedByLady = true;
        $this->hasLady = true;
    }

    public function hasLady(){
        return $this->hasLady;
    }

    public function robLady(){
        $this->hasLady = false;
    }

    public function ladyCheck(){
        $this->giveLady();
        return $this->side;
    }

    public function checkedByLady(){
        return $this->checkedByLady;
    }

    public function setQuestMember(){
        $this->isQuestMember = true;
    }

    public function isQuestMember(){
        return $this->isQuestMember;
    }

    public function resetQuestMember(){
        $this->isQuestMember = false;
    }
}