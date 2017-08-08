<?php
namespace phptoy\stream;
/**
 * Class ByteArray
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
    public function isEnd() : bool {
        return ($this->pos === $this->size);
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
        $str=$this->readBytes($len);
        $bits=$len * 8;
        return IntUtil::unpack($str, $bits, $signType);
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
     * @param int $len
     *
     * @return string
     */
    private function readBytes(int $len=1) : string {
        $count=$this->pos + $len;
        $buf='';
        if ($count > $this->size - 1) {
            return $buf;
        }
        $buf=substr($this->buf, $this->pos, $len);
        $this->pos+=$len;
        return $buf;
    }
}