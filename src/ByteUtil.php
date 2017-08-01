<?php
namespace phptoy\stream;

use SplFixedArray;

/**
 * Class ByteUtil
 *
 * @package phptoy\streeam
 */
final class ByteUtil {
    private static $isBigEndian=null;

    /**
     * @param string $data
     *
     * @return array
     */
    public static function toArray(string $data) : array {
        return unpack('C*', $data);
    }

    /**
     * @param string $data
     *
     * @return SplFixedArray
     */
    public static function toFixedArray(string $data) : SplFixedArray {
        return SplFixedArray::fromArray(static::toArray($data));
    }

    /**
     * @param array $bytes
     *
     * @return string
     */
    public static function toString(array $bytes) : string {
        return pack('C*', ...$bytes);
    }

    /**
     * hex string
     *
     * @param array $bytes
     *
     * @return string
     */
    public static function formatToHex(array $bytes) : string {
        $str='';
        foreach ($bytes as $byte) {
            $str.=' ' . substr('00' . base_convert(intval($byte), 10, 16), -2);
        }
        return trim($str);
    }

    /**
     * @return bool
     */
    public static function hostIsBigEndian() : bool {
        if (null === static::$isBigEndian) {
            static::$isBigEndian=(pack('L', 1) === pack('N', 1));
        }
        return static::$isBigEndian;
    }

    /**
     * Host byte order convert to network(big endian)
     *
     * @param string|array $data
     *
     * @return array|bool|string
     */
    public static function hostToNetwork($data) {
        return static::byteOrderChange($data);
    }

    /**
     * network convert to host byte order
     *
     * @param string|array $data
     *
     * @return array|bool|string
     */
    public static function networkToHost($data) {
        return static::byteOrderChange($data);
    }

    /**
     * byte order covert
     *
     * @param string|array $data
     *
     * @return array|bool|string
     */
    public static function byteOrderChange($data) {
        if ((!is_string($data) && !is_array($data)) || !$data) {
            return false;
        }
        if (static::hostIsBigEndian()) {
            return $data;
        }
        $convert=is_string($data) ? 'strrev' : 'array_reverse';
        return $convert($data);
    }
}