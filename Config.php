<?PHP
namespace Configuration;
class Configuration { // singleton
    const AUTO = 0;
    const JSON = 2;
    const PHP_INI = 4;
    const XML = 16;

    static private $CONF_EXT = array(
        'json' => 2, // JSON
        'ini' => 4,  // PHP_INI
        'xml' => 16  // XML
    );

    static private $instances;

    private $data;

    static public function objectToArray($obj) {
        $arr = (is_object($obj))?
            get_object_vars($obj) :
            $obj;

        foreach ($arr as $key => $val) {
            $arr[$key] = ((is_array($val)) || (is_object($val)))?
                self::objectToArray($val) :
                $val;
        }

        return $arr;
    }

    private function __construct($file, $type = Configuration::AUTO) {
        if ($type == self::AUTO) {
            $type = self::$CONF_EXT[pathinfo($file, PATHINFO_EXTENSION)];
        }

        switch($type) {
            case self::JSON:
                $this->data = json_decode(file_get_contents($file), true);
                break;

            case self::PHP_INI:
                $this->data = parse_ini_file($file, true);
                break;

            case self::XML:
                $this->data = self::objectToArray(simplexml_load_file($file));
                break;
        }
    }

    static public function & getInstance($file, $type = Configuration::AUTO) {
        if(! isset(self::$instances[$file])) {
            self::$instances[$file] = new Configuration($file, $type);
        }

        return self::$instances[$file];
    }

    public function __get($section) {
        if ((is_array($this->data)) &&
                (array_key_exists($section, $this->data))) {
            return $this->data[$section];
        }
    }

    public function getSections() {
        return array_keys($this->data);
    }
}

$Config = Configuration::getInstance(/*config filename*/);
foreach($Config->getSections() as $sectionName) {
    //var_dump($sectionName);
    var_dump($Config->{$sectionName});
}
?>