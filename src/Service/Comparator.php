<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Service;


/**
 * Class Comparator
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Service
 */
class Comparator
{
    /**
     * Сервис для сравнения значений
     *
     * @param mixed $expectedValue
     * @param mixed $actualValue
     * @param string $expectedValueType
     *
     * @return bool
     * @throws \OldTown\Workflow\ZF2\Toolkit\Validator\Exception\InvalidConvertTypeException
     * @throws \OldTown\Workflow\ZF2\Toolkit\Service\Exception\InvalidConvertTypeException
     */
    public function compare($expectedValue, $actualValue, $expectedValueType = null)
    {

        $typeExpectedValue = is_string($expectedValueType) ? $expectedValueType : 'string';
        $expected = $this->getExpectedValue($expectedValue, $typeExpectedValue);

        return $expected === $actualValue;
    }


    /**
     * Подготавливает значение эталонного выражения
     *
     * @param $rawExpectedValue
     * @param $typeExpectedValue
     *
     * @return mixed
     * @throws \OldTown\Workflow\ZF2\Toolkit\Service\Exception\InvalidConvertTypeException
     * @throws \OldTown\Workflow\ZF2\Toolkit\Validator\Exception\InvalidConvertTypeException
     */
    protected function getExpectedValue($rawExpectedValue, $typeExpectedValue)
    {
        $result = @settype($rawExpectedValue, $typeExpectedValue);

        if (!$result) {
            throw new Exception\InvalidConvertTypeException('Invalid convert type');
        }

        return $rawExpectedValue;
    }
}
