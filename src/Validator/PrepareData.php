<?php
/**
 * @link  https://github.com/old-town/workflow-zf2-toolkit
 * @author  Malofeykin Andrey  <and-rey2@yandex.ru>
 */
namespace OldTown\Workflow\ZF2\Toolkit\Validator;

use Zend\Validator\AbstractValidator;
use OldTown\Workflow\ZF2\Dispatch\Dispatcher\WorkflowDispatchEventInterface;


/**
 * Class PrepareData
 *
 * @package OldTown\Workflow\ZF2\Toolkit\Validator
 */
class PrepareData extends AbstractValidator
{
    /**
     * @var string
     */
    const VALIDATE_VALUE_INVALID        = 'validateValueInvalid';

    /**
     * @var string
     */
    const ERROR_CONVERT_EXPECTED_VALUE        = 'errorConvertExpectedValue';

    /**
     * @var string
     */
    const PARAM_NOT_FOUND_IN_PREPARE_DATA = 'paramNotFoundInPrepareData';

    /**
     * @var string
     */
    const PARAM_NAME_IN_PREPARE_DATA_INVALID = 'paramNameInPrepareDataInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = [
        //self::HTTP_METHOD_INVALID      => 'Http method invalid',
        self::VALIDATE_VALUE_INVALID      => 'Validate value not implement WorkflowDispatchEventInterface',
        self::PARAM_NAME_IN_PREPARE_DATA_INVALID => 'Param name in prepare data invalid',
        self::PARAM_NOT_FOUND_IN_PREPARE_DATA => 'Param "%paramNameInPrepareData%" not found in prepare data',
        self::ERROR_CONVERT_EXPECTED_VALUE => 'Error convert expected value in type "%typeExpectedValue%"'
    ];

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $messageVariables = [
        'paramNameInPrepareData' => ['options' => 'paramNameInPrepareData'],
        'typeExpectedValue' => ['options' => 'typeExpectedValue'],
    ];

    /**
     * @var array
     */
    protected $options = [
        'paramNameInPrepareData' => null,
        'typeExpectedValue' => null,
        'expectedValue' => null,
    ];

    /**
     * @param WorkflowDispatchEventInterface $value
     *
     * @return bool
     * @throws \OldTown\Workflow\ZF2\Toolkit\Validator\Exception\InvalidConvertTypeException
     */
    public function isValid($value)
    {
        if (!$value instanceof WorkflowDispatchEventInterface) {
            $this->error(self::VALIDATE_VALUE_INVALID);
            return false;
        }

        $name = array_key_exists('paramNameInPrepareData', $this->options) ? $this->options['paramNameInPrepareData'] : null;
        if (!is_string($name)) {
            $this->error(self::PARAM_NAME_IN_PREPARE_DATA_INVALID);
            return false;
        }
        $prepareData =  $value->getPrepareData();

        if (!array_key_exists($name, $prepareData)) {
            $this->error(self::PARAM_NOT_FOUND_IN_PREPARE_DATA);
            return false;
        }

        $actualValue = $prepareData[$name];

        $typeExpectedValue = array_key_exists('typeExpectedValue', $this->options) ? $this->options['typeExpectedValue'] : 'string';

        $rawExpectedValue = array_key_exists('expectedValue', $this->options) ? $this->options['expectedValue'] : null;

        try {
            $expectedValue = $this->getExpectedValue($rawExpectedValue, $typeExpectedValue);
        } catch (Exception\InvalidConvertTypeException $e) {
            $this->error(self::ERROR_CONVERT_EXPECTED_VALUE);
            return false;
        }

        return $expectedValue === $actualValue;
    }


    /**
     * Подготавливает значение эталонного выражения
     *
     * @param $rawExpectedValue
     * @param $typeExpectedValue
     *
     * @return mixed
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
