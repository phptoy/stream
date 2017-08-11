<?php
namespace phptoy\stream;

use SplFixedArray;

/**
 * Class ByteStream
 *
 * @package mobar\stream
 */
class ByteStream {
    /**
     * @var string $buf
     */
    private $buf;
    /**
     * @var int $size
     */
    private $size;
    /**
     * @var int $pos
     */
    private $pos;
    /**
     * @var int $mark
     */
    private $mark;

    /**
     * ByteArray constructor.
     *
     * @param string $data
     */
    public function __construct(string $data) {
        $this->buf=$data;
        $this->size=strlen($data);
        $this->pos=0;
        $this->mark=0;
    }

    /**
     * @return bool
     */
    public function hasRemaining() : bool {
        return ($this->size > $this->pos);
    }

    /**
     * @return int
     */
    public function length() : int {
        return $this->size;
    }

    public function mark() : void {
        $this->mark=$this->pos;
    }

    public function resetMark() : void {
        $this->pos=$this->mark;
        $this->mark=0;
    }

    /**
     * @return int
     */
    public function position() : int {
        return $this->pos;
    }

    /**
     * read a byte
     *
     * @return int
     */
    public function read() : int {
        $bin=$this->_readStringByLength(1);
        if (!$bin) {
            return -1;
        }
        return IntUtil::unpack($bin, 8, IntUtil::UNSIGNED);
    }

    /**
     * @param int $len
     *
     * @return SplFixedArray
     */
    public function readBytes(int $len) : SplFixedArray {
        $str=$this->readString($len);
        if (!$str) {
            return new SplFixedArray(0);
        }
        return SplFixedArray::fromArray(unpack('C*', $str)[1]);
    }

    /**
     * read $len int
     *
     * @param int $len
     * @param int $signType
     *
     * @return int
     */
    public function readInt(int $len, int $signType=IntUtil::UNSIGNED) : int {
        $bin=$this->_readStringByLength($len);
        if (!$bin) {
            return -1;
        }
        $bits=$len * 8;
        return IntUtil::unpack($bin, $bits, $signType);
    }

    /**
     * @return int
     */
    public function readInteger() : int {
        return $this->readInt(4);
    }

    /**
     * @return int
     */
    public function readLong() : int {
        return $this->readInt(8);
    }

    /**
     * @return int
     */
    public function readShort() : int {
        return $this->readInt(2);
    }

    /**
     * read $len byte
     *
     * @param int $len
     *
     * @return string
     */
    public function readString(int $len) : string {
        return $this->_readStringByLength($len);
    }

    /**
     * @param int $len
     */
    public function skip(int $len=1) : void {
        $pos=$this->pos+=$len;
        if ($pos > $this->size - 1) {
            $this->pos=$this->size;
        } else {
            $this->pos=$pos;
        }
    }

    /**
     * @param bool $isFixed
     *
     * @return array|SplFixedArray
     */
    public function toArray($isFixed=false) {
        $bin=substr($this->buf, $this->pos);
        $result=unpack('C*', $bin);
        if (!$isFixed) {
            return $result;
        }
        return SplFixedArray::fromArray($result);
    }

    /**
     * @param int $len
     *
     * @return string
     */
    private function _readStringByLength(int $len=1) : string {
        $count=$this->pos + $len;
        $bin='';
        if ($count > $this->size - 1) {
            return $bin;
        }
        $bin=substr($this->buf, $this->pos, $len);
        $this->pos+=$len;
        return $bin;
    }
}