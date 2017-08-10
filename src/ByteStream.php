<?php
namespace phptoy\stream;

use SplFixedArray;

/**
 * Class ByteArray
 *
 * @package mobar\stream
 */
class ByteStream {
    /**
     * @var string $buf
     */
    protected $buf;
    /**
     * @var int $size
     */
    protected $size;
    /**
     * @var int $pos
     */
    protected $pos;
    /**
     * @var int $mark
     */
    protected $mark;

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
     * @return int
     */
    public function getPosition() : int {
        return $this->pos;
    }

    /**
     * @return bool
     */
    public function hasRemaining():bool{
        return ($this->size>$this->pos);
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


    /**
     * read $len byte
     *
     * @param int $len
     *
     * @return string
     */
    public function read(int $len) : string {
        return $this->readBytes($len);
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
        $bin=$this->readBytes($len);
        $bits=$len * 8;
        return IntUtil::unpack($bin, $bits, $signType);
    }

    /**
     * @param int $len
     *
     * @return string
     */
    public function readString(int $len) : string {
        return $this->readBytes($len);
    }

    public function reset() : void {
        $this->pos=$this->mark;
        $this->mark=0;
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
    private function readBytes(int $len=1) : string {
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