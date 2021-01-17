<?php /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/**
 * Created by PhpStorm.
 * User: katlego
 * Date: 2018/05/16
 * Time: 05:35
 */

namespace geekpop;
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

/**class declarations*/

use DateTime;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use mediaburst\ClockworkSMS\Clockwork;
use mediaburst\ClockworkSMS\ClockworkException;
use PDO;
use stdClass;


class GeekPop
{
    /**
     * @throws Exception
     */

    /**
     * @param $const
     * @return PDO
     */
    public static function DataBase($const)
    {
        try {
            $db = new PDO($const['dsn'], $const['user'], $const['pass']);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (\PDOException $e) {
            die('oops!');
        }
        return $db;
    }

    /**
     * @param $array
     * @return stdClass
     */
    public function Object($array)
    {
        $object = new stdClass();
        foreach (is_array($array) ? $array : [] as $key => $value) {
            if (is_array($value)) {
                $value = (new self)->Object($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    /**
     * @param $value
     * @param $operator
     * @return bool|float|int
     */
    public function Gigabyte($value, $operator)
    {
        $x = false;
        do {
            switch ($operator) {
                case true:
                    {
                        $x = $value * 1024 * 1024 * 1024;
                    }
                    break;
                case false:
                    {
                        $x = $value / 1024 / 1024 / 1024;
                    }
                    break;
                default:
                    break;
            }
            return $x;
        } while (0 < 4);
    }

    /**
     * @param $object
     * @param null $stdClass
     * @return mixed
     */
    public static function jsonDBIterator($object, $stdClass = null)
    {
        $cfg = new Config;
        $array = json_decode(@file_get_contents("JSON/database.json"), JSON_OBJECT_AS_ARRAY);
        return $stdClass ? (new self)->Object($array[$object]) : $array[$object];
    }

    /**
     * @param $array
     * @param null $isLightbox
     * @throws Exception
     */
    public function DisplayObject($array, $isLightbox = null)
    {
        $isLightbox ? $array['isLightbox'] = true : false;
        $arr = ["response" => 300, "modal" => $array];
        throw new Exception(self::ListObject($arr));
    }

    /**
     * @param $array
     * @return mixed
     */
    public function ListObject($array)
    {
        $object = json_encode($array, JSON_PRETTY_PRINT);
        $jsonObject = str_replace("'", '&#39;', $object);
        return $jsonObject;
    }

    /**
     * @param $delta
     * @return false|int
     */
    public function CurrencyFormat($delta)
    {
        return preg_match('/^[0-9]+(?:\.[0-9]+)?$/', $delta);

    }

    /**
     * @param $number
     * @return string
     */
    public function ordinal($number)
    {
        $format = new \NumberFormatter('za-EN', \NumberFormatter::ORDINAL);
        return $format->format($number);
    }

    /*accounting & money formating*/

    /**
     * @param $delta
     * @return float|int
     * @throws Exception
     */
    public function Interest($delta)
    {
        /*
        -> typical interest rate
        */
        return ($delta * self::PracticeSettings()->tax) / 100;
    }


    /*validators*/

    /**
     * @param $telephone
     * @param $index
     * @param null $country_code
     * @return mixed
     * @throws Exception
     */
    public function PhoneNumberHandler($telephone, $index, $country_code = null)
    {
        $array = [];
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            if ($country_code == true && gettype($country_code) == "boolean") {
                $numberPrototype = $phoneUtil->parse($telephone, self::clientip()->country, null, true);
            } elseif (gettype($country_code) == "string") {
                $numberPrototype = $phoneUtil->parse($telephone, $country_code, null, true);
            } else {
                $numberPrototype = $phoneUtil->parse($telephone, self::PracticeSettings()->code, null, true);
            }
            $array = [
                $phoneUtil->isValidNumber($numberPrototype),/*0 telephone validator*/
                $phoneUtil->format($numberPrototype, PhoneNumberFormat::E164),/*1 E164*/
                $phoneUtil->format($numberPrototype, PhoneNumberFormat::NATIONAL),/*2 national format*/
                $phoneUtil->format($numberPrototype, PhoneNumberFormat::INTERNATIONAL),/*3 international format*/
                $phoneUtil->getNumberType($numberPrototype)/*4 Number Type is mobile*/
            ];
        } catch (NumberParseException $e) {
        }
        return $array[$index];
    }

    /**
     * @return stdClass
     */
    private function clientip()
    {
        $ipaddress = null;
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = null;
        $object = json_decode(file_get_contents('https://www.iplocate.io/api/lookup/' . $ipaddress));
        return $object;
    }

    /**
     * @param $country
     * @return bool|stdClass
     */
    public function Region($country)
    {
        $region = self::jsonDBIterator('regions', true);
        /**
         * new device connecting to pallette will
         * need to connect to the region
         * the the request is being made from
         */
        return $region->{$country};
    }


    /**
     * @param $delta
     * @return string
     * @throws Exception
     */
    public function Currency($delta)
    {
        $format = new \NumberFormatter(self::PracticeSettings()->locale . ".utf-8", \NumberFormatter::CURRENCY);
        return $format->format($delta);
    }

    /*operations*/

    /**
     * @param $delta
     * @param $element
     * @return bool|float
     * @throws Exception
     */
    public function Parsecurrency($delta, $element)
    {
        $output = false;
        switch (is_numeric($delta)) {
            case true:
                {
                    $output = $delta;
                }
                break;
            case false:
                {
                    switch (preg_match('~[0-9]~', $delta)) {
                        case true:
                            {
                                $curr = 'en-ZA';
                                $format = new \NumberFormatter('en-ZA.utf-8', \NumberFormatter::CURRENCY);
                                $output = $format->parseCurrency($delta, $curr);
                            }
                            break;
                        case false:
                            {
                                $string = "that's an invalid currency expression <br>your currency expression should be like this: <strong>XXX</strong> or <strong>XXX.XX</strong>";
                                self::InputErrors($string, 2, $element);
                            }
                            break;
                        default:
                            break;
                    }
                }
                break;
            default:
                break;
        }
        return $output;
    }

    /**
     * @param $delta
     * @param $index
     * @param $element
     * @param $isLightBox
     * @throws Exception
     */
    public function InputErrors($delta, $index, $element, $isLightBox = null)
    {
        $val = [
            0 => addslashes("please populate the <strong><i>{$delta}</i></strong> field"),
            1 => addslashes("that&#39;s not a valid {$delta}"),
            2 => addslashes("{$delta}"),
            3 => addslashes("this file exceeds the prescibed limit"),
            4 => addslashes("collusion : <strong><i>{$delta}</i></strong> is unavailabe for usage"),
            5 => addslashes("invalid entry: <strong><i>{$delta}</i></strong> is not a numerical value"),
        ];
        $array = (object)["response" => 1,
            "key" => is_array($element) ? $element[0] : $element,
            "message" => $val[$index],
            "type" => "warning",
        ];
        $isLightBox ? $array->isLightBox = true : false;
        throw new Exception(self::ListObject($array));
    }

    /**
     * @param $message
     * @param bool $isLightbox
     * @param null $stack
     * @throws Exception
     */
    public function UploadErrors($message, $stack = null, $isLightbox = false)
    {
        $val = addslashes($message);
        $object = (object)[
            "response" => 300,
            "upload" => (object)[
                "type" => "warning",
                "key" => false,
                "message" => $val,
            ],
        ];
        /** add stack to $arrayForLigtBox */
        $stack ? $object->upload->stack = $stack : false;
        $isLightbox ? $object->upload->isLightbox = true : false;
        throw new Exception(self::ListObject($object));
    }

    /**
     * @param $delta
     * @return bool
     */
    public function numeric($delta)
    {
        return is_numeric($delta);
    }

    /**
     * @param $delta
     * @return mixed
     */
    public function ValidateEmail($delta)
    {
        return filter_var($delta, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $delta
     * @return false|int
     */
    public function ValidateWeb($delta)
    {
        return filter_var($delta, FILTER_VALIDATE_URL);
    }

    /**
     * @param $delta
     * @return false|int
     */
    public function ValidateMobile($delta)
    {
        return preg_match('/^[0-9]{10}+$/', $delta);
    }

    /**
     * @param $parameter
     * @param $target
     * @return bool
     * @throws Exception
     */

    /**
     * @param $haystack
     * @return false|int
     */
    public function wholeword($haystack)
    {
        return preg_match('/\bPLEA\b/', $haystack);
    }

    /**
     * @param $mobile
     * @return string
     */
    public function last4($mobile)
    {
        return str_repeat("*", strlen($mobile) - 4) . substr($mobile, -4);
    }

    /**
     * @param $array
     * @return array
     */
    public function RandErr($array)
    {

        return $array[array_rand(is_array($array) ? $array : [])];
    }

    /**
     * @param $object
     * @return mixed
     */
    public function to_Array($object)
    {
        return json_decode(is_object($object) ? json_encode($object) : "{}", JSON_OBJECT_AS_ARRAY);
    }

    /**
     * @param $hex
     * @param $steps
     * @return string
     */
    public function ColourScheme($hex, $steps)
    {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));
        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }
        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';
        foreach ($color_parts as $color) {
            $color = hexdec($color); // Convert to decimal
            $color = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }
        return $return;
    }

    /**
     * @param $string
     * @return string
     */
    public function Acronym($string)
    {
        $words = explode(" ", $string);
        $acronym = "";
        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        return strtoupper($acronym);
    }

    /**
     * @param $number
     * @return string
     */
    public function versioncontrol($number)
    {
        return base_convert($number, 10, 3);
    }

    /**
     * @param $number
     * @return string
     */
    public function CustomerID($number)
    {
        $randlibfactory = new \RandomLib\Factory;
        $gmediumgen = $randlibfactory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
        return $gmediumgen->generateString(4, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') . base_convert($number + self::OTP(5), 10, 6);
    }

    /**
     * @param $digits
     * @return stdClass
     */
    public function OTP($digits)
    {
        $randlibfactory = new \RandomLib\Factory;
        $gmediumgen = $randlibfactory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::LOW));
        $int = $gmediumgen->generateInt($digits);
        /** code sent to server */
        $server = substr($int, 0, $digits / 2) . substr($int, $digits / 2, $digits / 2);
        /** code sent to user for easier readabilty */
        $user = substr($int, 0, $digits / 2) . '-' . substr($int, $digits / 2, $digits / 2);
        return self::Object(["server" => $server, "user" => $user]);
    }

    /**
     * @param $string
     * @param $array
     * @return string
     */
    public function ReplaceString($string, $array)
    {
        return strtr($string, $array);
    }


    /**
     * @return array|stdClass
     */
    public function Row()
    {
        $uniqid = uniqid(true);
        $array = ['contain' => 'con' . $uniqid, 'element' => 'el' . $uniqid, 'root' => 'rt' . $uniqid, 'prop' => 'prop' . $uniqid];
        return self::Object($array);
    }

    /**
     * @param $string
     * @return bool|int
     */
    public function auth($string)
    {
        return preg_match("/\b1\b/i", $string);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function reference($index)
    {
        $components = [PHP_URL_HOST, PHP_URL_PATH, PHP_URL_QUERY];
        return parse_url($_SERVER['HTTP_REFERER'], $components[$index]);
    }

    /**
     * @return string
     */
    public function password()
    {
        $factory = new \RandomLib\Factory;
        $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
        do {
            $password = $generator->generateString(8, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~`@#$%^&*()_-+={}:;/,<.>?');
            $test = preg_match('~[0-9]~', $password);
            $test2 = preg_match('~[a-f]~', $password);
            $test3 = preg_match('/[!@#$%^&*_+=|<,.>?`~{}]/', $password);
        } while (!$test || !$test2 || !$test3);
        return $password;
    }

    /**
     * @param $country_code
     * @return array
     */
    public function TimeZone($country_code)
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country_code);
    }

    /**
     * @param $string
     * @param $devisor
     * @param $return
     * @return false|int
     */
    public function YearPeriods($string, $devisor, $return)
    {
        if ($return) {
            $ceiling =
                (
                    ceil
                    (
                        date
                        ("m",
                            strtotime($string) / $devisor
                        )
                    ) * $devisor
                ) - ($devisor - 1);
            $val = mktime
            (
                0,
                0,
                0,
                $ceiling . null,
                date(01) + 0,
                date("Y", strtotime("-1 year")) . null
            );
        } else {
            $floor =
                (
                    floor
                    (
                        date
                        ("m",
                            strtotime($string) / $devisor
                        )
                    ) * $devisor
                );
            $val = mktime
            (
                0,
                0,
                0,
                $floor . null,
                date('t') + 0,
                date("Y", strtotime("-1 year")) . null
            );
        }
        return $val;
    }

    /**
     * @param null $when
     * @return false|string
     * @throws Exception
     */
    public function AccountingTimeTracker($when = null)
    {
        $dateTime = new DateTime();
        $object = [
            "year" => $dateTime->format("Y"),
            "year_week" => "week {$dateTime->format("W")}",
            "month" => $dateTime->format("m"),
            "month_week" => self::WeekOfMonth($when),
            "day" => $dateTime->format("l"),
        ];
        return json_encode($object);
    }

    /**
     * @param null $when
     * @return false|int|string
     */
    private static function WeekOfMonth($when = null)
    {
        if ($when == null) $when = time();
        /** ISO weeks start on Monday */
        $week = date('W', $when);
        $firstWeekOfMonth = date('W', strtotime(date('Y-m-01', $when)));
        $monthWeek = $week < $firstWeekOfMonth ? $week : ($week - $firstWeekOfMonth);
        return "week {$monthWeek}";
    }

    /**
     * @param $secs
     * @return string
     */
    public function TimeAlapsed($secs)
    {
        $ret = [];
        $bit = [
            'year' => ' ' . ($secs / 31556926 % 12),
            'week' => ' ' . ($secs / 604800 % 52),
            'day' => ' ' . ($secs / 86400 % 7),
            'hour' => ' ' . ($secs / 3600 % 24),
            'minute' => ' ' . ($secs / 60 % 60),
            'second' => ' ' . ($secs % 60),
        ];
        foreach ($bit as $k => $v) {
            $v > 0 ?
                $ret[$k] = $v . ' ' . $k . ($v > 1 ? 's' : false)
                : $ret[$k] = null;
        }
        foreach ($ret as $k => $v) {
            if (is_null($v)) {
                unset($ret[$k]);
            }
        }
        return implode(',', array_slice($ret, 0, 2));
    }

    /**
     * @param $category
     * @return bool|stdClass
     */
    public function PricingPlans($category)
    {
        $objitems = self::jsonDBIterator('pricing_plans');
        foreach ($objitems as $plans) {
            return self::Object($plans[$category]);
        }
        return false;
    }

    /**
     * @param $array
     */
    public function SMSFree($array)
    {
        $clockwork = new Clockwork(SMS['api_key']);
        try {
            $message = ['to' => $array['mobile'], 'message' => $array['body']];
            $clockwork->send($message);
        } catch (ClockworkException $e) {
        }
    }

}