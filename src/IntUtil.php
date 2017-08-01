<?php
namespace phptoy\stream;
/**
 * Class IntUtil
 *
 * @package phptoy\stream
 */
final class IntUtil {
    const SIGNED=0;//signed
    const UNSIGNED=1; //unsigned machine byte order
    const UNSIGNED_LITTLE_ENDIAN=2; //unsigned little endian byte order
    const UNSIGNED_BIG_ENDIAN=3; //unsigned big endian byte order
    private const SUPPORTED=[8, 16, 24, 32, 40, 48, 56, 64];
    private static $machineByteOrder=null;

    /**
     * @param int $int
     * @param int $bits
     * @param int $signType
     *            0:signed
     *            1:unsigned machine order
     *            2:unsigned little endian
     *            3:unsigned big endian
     *
     * @return null|string
     */
    public static function pack(int $int, int $bits=8, int $signType=self::SIGNED) : ?string {
        if (false === in_array($bits, static::SUPPORTED)) {
            return null;
        }
        if (24 === $bits) {
            $h=($int & 0xffff00) >> 8;
            $l=$int & 0xff;
            $format=static::SIGNED === $signType ? 'sc' : 'SC';
            $bin=pack($format, $h, $l);
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                return strrev($bin);
            }
            return $bin;
        }
        if (40 === $bits) {
            $h=($int & 0xffffffff00) >> 8;
            $l=$int & 0xff;
            $format=static::SIGNED === $signType ? 'lc' : 'LC';
            $bin=pack($format, $h, $l);
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                return strrev($bin);
            }
            return $bin;
        }
        if (48 === $bits) {
            $h=($int & 0xffffffff0000) >> 16;
            $l=$int & 0xffff;
            $format=static::SIGNED === $signType ? 'ls' : 'LS';
            $bin=pack($format, $h, $l);
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                return strrev($bin);
            }
            return $bin;
        }
        if (56 === $bits) {
            $h=($int & 0xffffffff000000) >> 24;
            $m=($int & 0xffff00) >> 8;
            $l=$int & 0xff;
            $format=static::SIGNED === $signType ? 'lsc' : 'LSC';
            $bin=pack($format, $h, $m, $l);
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                return strrev($bin);
            }
            return $bin;
        }
        $format=static::getFormat($bits, $signType);
        return pack($format, $int);
    }

    /**
     * @param string $bin
     * @param int    $bits
     * @param int    $signType
     *            0:signed
     *            1:unsigned machine order
     *            2:unsigned little endian
     *            3:unsigned big endian
     *
     * @return int
     */
    public static function unpack(string $bin, int $bits=8, int $signType=self::SIGNED) : int {
        if (false === in_array($bits, static::SUPPORTED)) {
            return -1;
        }
        if (24 === $bits) {//fixed
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                $result=unpack('Ca/Sb', $bin);
                return ($result['a'] + ($result['b'] << 8));
            }
            $format=static::SIGNED === $signType ? 'sa/cb' : 'Sa/Cb';
            $result=unpack($format, $bin);
            $int=($result['a'] << 8) + $result['b'];
            return $int;
        }
        if (40 === $bits) {//5 byte int
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                $result=unpack('Ca/Lb', $bin);
                return ($result['a'] + ($result['b'] << 8));
            }
            $format=static::SIGNED === $signType ? 'la/cb' : 'La/Cb';
            $result=unpack($format, $bin);
            $int=($result['a'] << 8) + $result['b'];
            return $int;
        }
        if (48 === $bits) {//6 byte int
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                $result=unpack('Sa/Lb', $bin);
                return ($result['a'] + ($result['b'] << 16));
            }
            $format=static::SIGNED === $signType ? 'la/sb' : 'La/Sb';
            $result=unpack($format, $bin);
            $int=($result['a'] << 16) + $result['b'];
            return $int;
        }
        if (56 === $bits) {
            if ($signType > static::UNSIGNED && static::machineByteOrder() !== $signType) {
                $result=unpack('Ca/Sb/Lc', $bin);
                return ($result['a'] + ($result['b'] << 8) + ($result['c'] << 24));
            }
            $format=static::SIGNED === $signType ? 'la/sb/cc' : 'La/Sb/Cc';
            $result=unpack($format, $bin);
            $int=($result['a'] << 24) + ($result['b'] << 8) + $result['c'];
            return $int;
        }
        $format=static::getFormat($bits, $signType);
        return unpack($format, $bin)[1];
    }

    /**
     * get pack or unpack format
     *
     * @param int $bits
     * @param int $signType
     *
     * @return string
     */
    private static function getFormat(int $bits, int $signType) : string {
        if (16 === $bits) {//2 bytes
            if ($signType > static::UNSIGNED) {//unsigned short(little:big endian)
                return (static::UNSIGNED_BIG_ENDIAN !== $signType ? 'v' : 'n');
            }
            return (static::SIGNED === $signType ? 's' : 'S');//machine byte order
        }
        if (24 === $bits) {
            return (static::SIGNED === $signType ? 'ca/cb/cc' : 'Ca/Cb/Cc');//machine byte order
        }
        if (32 === $bits) {//4 bytes
            if ($signType > static::UNSIGNED) {//unsigned long(little:big endian)
                return (static::UNSIGNED_BIG_ENDIAN !== $signType ? 'V' : 'N');
            }
            return (static::SIGNED === $signType ? 'l' : 'L');//machine byte order
        }
        if (64 === $bits) {//8 bytes
            if ($signType > static::UNSIGNED) {//unsigned long long(little:big endian)
                return (static::UNSIGNED_BIG_ENDIAN !== $signType ? 'P' : 'J');
            }
            return ($signType ? 'q' : 'Q'); //machine byte order
        }
        //1 byte,signed char : unsigned char(byte)
        return (static::SIGNED === $signType ? 'c' : 'C');
    }

    /**
     * @return int
     */
    private static function machineByteOrder() : int {
        if (null === static::$machineByteOrder) {
            $isBE=(pack('L', 1) === pack('N', 1));
            static::$machineByteOrder=$isBE ? static::UNSIGNED_BIG_ENDIAN : static::UNSIGNED_LITTLE_ENDIAN;
        }
        return static::$machineByteOrder;
    }
}