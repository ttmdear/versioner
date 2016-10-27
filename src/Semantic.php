<?php
namespace Versioner;

class Semantic
{
    CONST UPGRADE = 'upgrade';
    CONST DOWNGRADE = 'downgrade';
    CONST COMPATIBLE = 'compatible';

    private $version;
    private $available;
    private $separator = '.';

    function __construct($version, $available = array(), $separator = '.')
    {
        $this->version = $version;
        $this->separator = $separator;
        $this->available = $available;
        $separator = $this->separator;

        // i don not know why php warn that string is given to usort
        @usort($this->available, function($a, $b) use($separator){
            $a = explode($separator, $a);
            $b = explode($separator, $b);
            $max = max(array(count($a), count($b)));

            $a = $this->fillArray($a, $max);
            $b = $this->fillArray($b, $max);

            for($i=0; $i<$max; $i++){
                if ($a[$i] < $b[$i]) {
                    return 1;
                }elseif($a[$i] > $b[$i]){
                    return -1;
                }
            }

            return 0;
        });
    }

    public function valid($with)
    {
        if ($this->analyze($with) === self::COMPATIBLE) {
            return true;
        }

        return false;
    }

    public function analyze($with)
    {
        $version = explode($this->separator, $this->version);
        $with = explode($this->separator, $with);
        $max = max(array(count($version), count($with)));

        $version = $this->fillArray($version, $max);
        $with = $this->fillArray($with, $max);

        if ($version[0] < $with[0]) {
            // 1.2.3 : core
            // 2.2.3 : need
            // then core must be upgrade
            return self::UPGRADE;
        }elseif($version[0] > $with[0]){
            return self::DOWNGRADE;
        }

        for($i=1; $i<$max; $i++){
            if ($version[$i] < $with[$i]) {
                // 1.2.3 : core
                // 1.4.3 : need
                // then core must be upgrade
                return self::UPGRADE;
            }
        }

        return self::COMPATIBLE;
    }

    public function getMatching($with)
    {
        foreach ($this->available as $version) {
            $semantic = new self($version, $with);

            if ($semantic->valid($with)) {
                return $version;
            }
        }

        return null;
    }

    private function fillArray($array, $to)
    {
        $to = $to - count($array);

        while($to > 0){
            $array[] = 0;
            $to--;
        }

        return $array;
    }
}
