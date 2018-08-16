<?php
/**
 * User: yz.chen
 * Time: 2018-07-13 11:06
 */

namespace Dandelion;


use Dandelion\Models\ModelBase;

class Validator
{
    public static $STR_LEN = 1;
    public static $STR_MATCH = 2;
    public static $NUM_RANGE = 3;
    public static $NUM_IS_INT = 4;
    public static $ID_CHECK = 5;

    protected $message = null;
    protected $validationList = [];

    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 添加检查对象
     *
     * <code>
     * // 校验字符串长度（可用于数字），下限和上限为null表示不限制
     * $validator->add($str, Validator::$STR_LEN, [5, 10], "长度介于5到10之间");
     * // 正则校验字符串（可用于数字）
     * $validator->add($str, Validator::$STR_MATCH, "/^1[0-9]{10}$/", "不是手机号码");
     * // 校验数值范围（可用于字符串形式的数字），下限和上限为null表示不限制
     * $validator->add($num, Validator::$NUM_RANGE, [100, 999], "超出范围");
     * // 校验数值是否整数（可用于字符串形式的数字）
     * $validator->add($num, Validator::$NUM_IS_INT, null, "不是整数");
     * // 校验id对应的模型是否存在，模型的属性可选
     * $validator->add($id, Validator::$ID_CHECK, User::class, "不存在该用户");
     * $validator->add($id, Validator::$ID_CHECK, ["class" => User::class, "name" => "张三"], "不存在该用户");
     * </code>
     *
     * @param mixed        $var     需要校验的对象
     * @param int          $type    校验方式
     * @param string|array $options 选项
     * @param string       $errMsg  校验失败的错误信息，可选
     * @return Validator
     */
    public function add($var, $type, $options = null, $errMsg = "")
    {
        if (!in_array($type, [1, 2, 3, 4, 5])) {
            throw new \Error();
        }
        $this->validationList[] = [$var, $type, $options, $errMsg];
        return $this;
    }

    /**
     * 清除已经添加的未校验项
     */
    public function clear()
    {
        $this->message = null;
        $this->validationList = [];
    }

    /**
     * 校验所有已添加的待校验项
     *
     * @return bool
     */
    public function validate()
    {
        $pass = true;
        $item = array_shift($this->validationList);
        if (!$item) {
            return true;
        }
        [$var, $type, $options, $errMsg] = $item;
        switch ($type) {
            case static::$STR_LEN :
                $pass = $this->strLen($var, $options);
                break;
            case static::$STR_MATCH :
                $pass = $this->strMatch($var, $options);
                break;
            case static::$NUM_RANGE :
                $pass = $this->numRange($var, $options);
                break;
            case static::$NUM_IS_INT :
                $pass = $this->numIsInt($var);
                break;
            case static::$ID_CHECK :
                $pass = $this->idCheck($var, $options);
                break;
        }
        if (!$pass) {
            $this->message = $errMsg;
            return false;
        }
        return $this->validate();
    }

    /**
     * @return bool
     */
    public function validateOrFail()
    {
        if ($this->validate()) {
            return true;
        }
        throw new HttpError($this->message, 400);
    }

    protected function strLen($string, $options)
    {
        if (is_numeric($string)) {
            $string = (string)$string;
        }
        if (!is_string($string)) {
            return false;
        }
        $min = $options[0];
        $max = $options[1];
        if ((null !== $min && mb_strlen($string) < $min) || (null !== $max && mb_strlen($string) > $max)) {
            return false;
        }
        return true;
    }

    protected function strMatch($string, $pattern)
    {
        if (is_numeric($string)) {
            $string = (string)$string;
        }
        if (!is_string($string) || !preg_match($pattern, $string)) {
            return false;
        }
        return true;
    }

    protected function numRange($num, $options)
    {
        if (!is_numeric($num)) {
            return false;
        }
        $min = $options[0];
        $max = $options[1];
        if ((null !== $min && $num < $min) || (null !== $max && $num > $max)) {
            return false;
        }
        return true;
    }

    protected function numIsInt($num)
    {
        if (is_numeric($num)) {
            $num = +$num;
        }
        return is_int($num);
    }

    protected function idCheck($id, $options)
    {
        if (!$id) {
            return false;
        }
        if (is_string($options)) {
            /** @var  ModelBase $class */
            $class = $options;
            $properties = [];
        } else {
            $class = $options["class"];
            $properties = $options;
            unset($properties["class"]);
        }
        $model = $class::ID($id);
        if (!$model) {
            return false;
        }
        $properties = $properties ?: [];
        foreach ($properties as $key => $value) {
            if ($value != $model->$key) {
                return false;
            }
        }
        return true;
    }

}