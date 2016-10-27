<?php
namespace VersionValidator;

class Semantic
{
    CONST NEED_UPGRADE = 'upgrade';
    CONST NEED_DOWNGRADE = 'downgrade';
    CONST COMPATIBLE = 'compatible';

    private $version;
    private $needVersion;
    private $available = array();
    private $separator = '.';

    function __construct($version, $needVersion, $available = null)
    {
        $this->version = $version;
        $this->needVersion = $needVersion;

        if (!is_null($available)) {
            $this->available = $available;
        }
    }

    public function valid()
    {
        if ($this->analyze() === self::COMPATIBLE) {
            return true;
        }

        return false;
    }

    public function analyze()
    {
        $version = explode($this->separator, $this->version);
        $needVersion = explode($this->separator, $this->needVersion);
        $max = max(array(count($version), count($needVersion)));

        $version = $this->fillArray($version, $max);
        $needVersion = $this->fillArray($needVersion, $max);

        if ($version[0] < $needVersion[0]) {
            return self::NEED_UPGRADE;
        }elseif($version[0] > $needVersion[0]){
            return self::NEED_DOWNGRADE;
        }

        for($i=1; $i<$max; $i++){
            if ($version[$i] < $needVersion[$i]) {
                return self::NEED_UPGRADE;
            }
        }

        return self::COMPATIBLE;
    }

    public function getMatching()
    {
        $available = $this->available;
        rsort($available);
        sort($available);

        foreach ($available as $version) {
            $semantic = new self($version, $this->needVersion);

            if ($semantic->valid()) {
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

    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

}

$simple = new Semantic('2.4.1', '3.5.1', array('2.10.24', '1.2.1', '1.3.5', '1.1.1', '3.6.1', '2.30.1', '1.12.34'));

if (!$simple->valid()) {
    echo "Niepoprawny\n";

    // todo : to delete
    die(print_r($simple->getMatching(), true));
    // endtodo
}
